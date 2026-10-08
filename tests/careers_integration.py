"""Local integration test. Uses disposable rows and a loopback PHP server.
Default: mock email transport. --live-email sends one explicitly requested test
to the configured HR recipient and --applicant (never uses production applicants).
"""
import struct, contextlib, argparse, base64, http.cookiejar, io, json, os, pathlib, re, secrets, socket, subprocess, tempfile, time, urllib.error, urllib.parse, urllib.request, zipfile
ROOT=pathlib.Path(__file__).resolve().parents[1]
PHP=r"C:\xampp\php\php.exe"
parser=argparse.ArgumentParser()
parser.add_argument("--live-email",action="store_true")
parser.add_argument("--applicant",default="candidate@example.test")
args=parser.parse_args()
marker="CareerTest-"+secrets.token_hex(6)
staff_email=marker.lower()+"@example.test"
password=secrets.token_hex(20)
staff_id=None
job_ids=[]
server=None
checks=0
def php(code):
    result=subprocess.run([PHP,"-r","require 'config/db_pdo.php'; $db=getPDO(); "+code],cwd=ROOT,capture_output=True,text=True,check=True)
    return result.stdout
def check(condition,label):
    global checks
    assert condition,label
    checks+=1
    print("PASS",label)
def jsonbody(body): return json.loads(body)
class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self,*args): return None
def client(): return urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()),NoRedirect())
def request(cli,path,fields=None,file=None):
    data=None; headers={}
    if fields is not None:
        if file:
            boundary="career"+secrets.token_hex(12);chunks=[]
            for k,v in fields.items():
                chunks.append(("--"+boundary+'\r\nContent-Disposition: form-data; name="'+k+'"\r\n\r\n'+str(v)+"\r\n").encode())
            filename,payload,mime=file
            chunks.append(("--"+boundary+'\r\nContent-Disposition: form-data; name="resume"; filename="'+filename+'"\r\nContent-Type: '+mime+"\r\n\r\n").encode()+payload+b"\r\n")
            chunks.append(("--"+boundary+"--\r\n").encode()); data=b"".join(chunks)
            headers["Content-Type"]="multipart/form-data; boundary="+boundary
        else:
            data=urllib.parse.urlencode(fields).encode();headers["Content-Type"]="application/x-www-form-urlencoded"
    try:
        with cli.open(urllib.request.Request(base+path,data=data,headers=headers),timeout=120) as r:return r.status,r.read(),dict(r.headers)
    except urllib.error.HTTPError as e:return e.code,e.read(),dict(e.headers)
def admin(action,fields=None,params=""):
    if fields is None:return request(staff,"/controllers/careers-admin.php?action="+action+params)
    return request(staff,"/controllers/careers-admin.php",dict(fields,action=action,csrf=csrf))
try:
    connection=php("echo $db->getAttribute(PDO::ATTR_CONNECTION_STATUS);")
    if "localhost" not in connection and "127.0.0.1" not in connection: raise RuntimeError("Tests require local MySQL.")
    payload=base64.b64encode(json.dumps([staff_email,password]).encode()).decode()
    staff_id=int(php("$v=json_decode(base64_decode('"+payload+"'),true);$s=$db->prepare('INSERT INTO staff (firstName,lastName,email,password,status) VALUES (?,?,?,?,?)');$s->execute(['Careers','Test',$v[0],password_hash($v[1],PASSWORD_DEFAULT),'Active']);echo $db->lastInsertId();"))
    with contextlib.ExitStack() as resources:
        temp = resources.enter_context(tempfile.TemporaryDirectory(prefix="solar-careers-test-"))
        temp=pathlib.Path(temp)
        env=os.environ.copy()
        mock=temp/"mock.php";mail_log=temp/"mail.jsonl"
        mock.write_text("""<?php
if (PHP_SAPI!=='cli-server') exit;
function solar_send_resend_email(string $to,string $subject,string $html,array $options=[]):array {
 file_put_contents(getenv('CAREERS_TEST_MAIL_LOG'),json_encode(['to'=>$to,'subject'=>$subject,'html'=>$html])."\\n",FILE_APPEND|LOCK_EX);
 if (is_file(getenv('CAREERS_TEST_FAIL_FILE'))) return ['success'=>false,'message'=>'Simulated provider outage'];
 return ['success'=>true,'message'=>'Test transport accepted'];
}
""",encoding="utf-8")
        fail_file=temp/"fail"
        if not args.live_email:
            env["CAREERS_HR_EMAIL"]="hr@example.test"
            env["CAREERS_TEST_MAIL_LOG"]=str(mail_log)
            env["CAREERS_TEST_FAIL_FILE"]=str(fail_file)
        sock=socket.socket();sock.bind(("127.0.0.1",0));port=sock.getsockname()[1];sock.close()
        base="http://127.0.0.1:"+str(port)
        command=[PHP,"-d","display_errors=0"]
        if not args.live_email:command+=["-d","auto_prepend_file="+str(mock)]
        command+=["-S","127.0.0.1:"+str(port),"-t",str(ROOT)]
        with open(temp/"server.log","wb") as log:
            server=subprocess.Popen(command,cwd=ROOT,env=env,stdout=log,stderr=log,creationflags=getattr(subprocess,"CREATE_NO_WINDOW",0))
            resources.callback(lambda: (server.terminate(), server.wait(timeout=10)))
            staff=client();public=client();anon=client()
            for i in range(50):
                try: request(public,"/careers.php");break
                except urllib.error.URLError: time.sleep(.1)
            status,body,_=request(anon,"/controllers/careers-admin.php")
            check(status==401,"anonymous admin access denied")
            status,body,_=request(staff,"/views/login.php",{"email":staff_email,"password":password})
            check(status==302,"disposable staff account logs in")
            status,body,_=request(staff,"/views/staff/dashboard.php")
            match=re.search(rb'data-csrf="([a-f0-9]+)"',body)
            check(bool(match),"Career Management is integrated into dashboard")
            csrf=match.group(1).decode()
            job=dict(title=marker,employment_type="Full-time",location="Muntinlupa",summary="Integration test vacancy; do not apply.",description="Test role",responsibilities="",qualifications="Engineering experience",skills="Solar experience",benefits="",deadline="",status="draft")
            status,body,_=request(staff,"/controllers/careers-admin.php",dict(job,action="save_job",csrf="wrong"))
            check(status==403,"staff CSRF enforced")
            status,body,_=admin("save_job",job);job_id=jsonbody(body)["id"];job_ids.append(job_id)
            check(status==200,"staff creates draft job")
            saved=next(j for j in jsonbody(admin("jobs")[1])["jobs"] if j["id"]==job_id)
            check(saved["responsibilities"]=="","responsibilities can be left blank")
            invalid_job=dict(job,id=job_id,qualifications="")
            check(admin("save_job",invalid_job)[0]==422,"at least one qualification is still required")

            status,body,_=request(public,"/careers.php")
            check(marker.encode() not in body,"draft job excluded from public list")
            job["id"]=job_id;job["status"]="published"
            check(admin("save_job",job)[0]==200,"staff publishes job")
            status,body,_=request(public,"/careers.php?job="+str(job_id))
            check(marker.encode() in body and b'id="career-application"' in body,"published job and application form appear")
            token=re.search(rb'name="csrf" value="([^"]+)"',body).group(1).decode()
            key=re.search(rb'name="request_key" value="([^"]+)"',body).group(1).decode()
            fields=dict(csrf=token,request_key=key,job_id=job_id,full_name="Careers Integration Test",email=args.applicant,phone="09170000000",message="TEST APPLICATION. Please disregard. <script>alert(1)</script>",consent="1")
            # Realistic minimal PDF fixture, kept local and deleted with this test.
            pdf=b"%PDF-1.4\n1 0 obj << /Type /Catalog >> endobj\ntrailer << /Root 1 0 R >>\n%%EOF\n"
            if not args.live_email:
                wrong=dict(fields,csrf="no")
                check(request(public,"/controllers/careers-apply.php",wrong,("cv.pdf",pdf,"application/pdf"))[0]==403,"public CSRF enforced")
                check(request(public,"/controllers/careers-apply.php",fields,("cv.pdf",b"<?php echo 1; ?>","application/pdf"))[0]==422,"spoofed PDF rejected")
                check(request(public,"/controllers/careers-apply.php",fields,("cv.exe",pdf,"application/pdf"))[0]==422,"disallowed extension rejected")
                check(request(public,"/controllers/careers-apply.php",fields,("cv.pdf",pdf+b"x"*(5*1024*1024),"application/pdf"))[0]==422,"oversize resume rejected")
                fail_file.touch()
            status,body,_=request(public,"/controllers/careers-apply.php",fields,("cv.pdf",pdf,"application/pdf"))
            check(status==200 and jsonbody(body)["success"],"application is accepted with confirmation")
            data=jsonbody(admin("applications",params="&search="+urllib.parse.quote(marker))[1])
            app=data["applications"][0];app_id=app["id"]
            check(data["total"]==1 and app["status"]=="New","application appears in staff list as New")
            details=jsonbody(admin("application",params="&id="+str(app_id))[1])
            check(details["application"]["message"]==fields["message"],"cover letter is preserved as plain text")
            if args.live_email:
                print("LIVE NOTIFICATIONS:",[(m["audience"],m["status"]) for m in details["emails"]])
                check(all(m["status"]=="sent" for m in details["emails"]),"Resend accepted HR and applicant notifications")
            else:
                check(all(m["status"]=="failed" for m in details["emails"]),"email failure does not lose application")
                fail_file.unlink()
                check(admin("retry_email",{"id":app_id})[0]==200,"staff retries failed notifications")
                details=jsonbody(admin("application",params="&id="+str(app_id))[1])
                check(all(m["status"]=="sent" for m in details["emails"]),"both notification recipients sent")
                count_before=len(mail_log.read_text().splitlines())
                admin("retry_email",{"id":app_id})
                check(len(mail_log.read_text().splitlines())==count_before,"retry skips already-sent notifications")
            check(request(anon,"/controllers/careers-resume.php?id="+str(app_id))[0]==401,"anonymous resume download denied")
            status,download,headers=request(staff,"/controllers/careers-resume.php?id="+str(app_id))
            check(status==200 and download==pdf and "attachment" in headers.get("Content-Disposition",""),"authorized resume download matches uploaded bytes")
            check(request(public,"/controllers/careers-apply.php",fields,("cv.pdf",pdf,"application/pdf"))[0]==200,"submission retry is idempotent")
            check(jsonbody(admin("applications",params="&search="+urllib.parse.quote(marker))[1])["total"]==1,"no duplicate application after retry")
            check(admin("update_application",{"id":app_id,"status":"Interview","internal_notes":"Internal test notes","archived":"1"})[0]==200,"staff changes status, notes and archives")
            check(jsonbody(admin("applications",params="&archived=1&search="+urllib.parse.quote(marker))[1])["total"]==1,"archived application retained")
            check(admin("update_application",{"id":app_id,"status":"Shortlisted","internal_notes":"Restored"})[0]==200,"application can be restored")
            if not args.live_email:
                # Real DOCX ZIP structure with Word content type.
                zip_bytes=io.BytesIO()
                with zipfile.ZipFile(zip_bytes,"w",zipfile.ZIP_DEFLATED) as z:
                    z.writestr("[Content_Types].xml",'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/></Types>')
                    z.writestr("word/document.xml",'<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body/></w:document>')
                fields["request_key"]=secrets.token_hex(32)
                status,body,_=request(public,"/controllers/careers-apply.php",fields,("cv.docx",zip_bytes.getvalue(),"application/vnd.openxmlformats-officedocument.wordprocessingml.document"))
                check(status==200,"DOCX accepted after archive content validation: "+body.decode()[:80])
                fields["request_key"]=secrets.token_hex(32)
                large=pdf+b" "* (2*1024*1024)
                check(request(public,"/controllers/careers-apply.php",fields,("large.pdf",large,"application/pdf"))[0]==200,"2 MB resume works with 1 MB MySQL packet limit")
                last=jsonbody(admin("applications",params="&search="+urllib.parse.quote(marker))[1])["applications"][0]
                check(request(staff,"/controllers/careers-resume.php?id="+str(last["id"]))[1]==large,"chunked large resume reconstructs correctly")
            if not args.live_email:
                # Minimal CFB Word fixture: header, FAT, directory, and WordDocument stream.
                header=bytearray(512);header[:8]=bytes.fromhex('d0cf11e0a1b11ae1')
                struct.pack_into('<HHHHH',header,24,0x3e,3,0xfffe,9,6)
                struct.pack_into('<IIIIIIIII',header,40,0,1,1,0,4096,0xfffffffe,0,0xfffffffe,0)
                struct.pack_into('<109I',header,76,0,*([0xffffffff]*108))
                fat=struct.pack('<128I',0xfffffffd,0xfffffffe,3,4,5,6,7,8,9,0xfffffffe,*([0xffffffff]*118))
                directory=bytearray(512)
                def directory_entry(offset,name,kind,start,size,child=0xffffffff):
                    name_bytes=(name+'\0').encode('utf-16le')
                    directory[offset:offset+len(name_bytes)]=name_bytes
                    struct.pack_into('<HBBIII',directory,offset+64,len(name_bytes),kind,1,0xffffffff,0xffffffff,child)
                    struct.pack_into('<IQ',directory,offset+116,start,size)
                directory_entry(0,'Root Entry',5,0xfffffffe,0,1)
                directory_entry(128,'WordDocument',2,2,4096)
                stream=bytearray(4096);struct.pack_into('<HH',stream,0,0xa5ec,0xc1)
                doc=bytes(header)+fat+bytes(directory)+bytes(stream)
                fields['request_key']=secrets.token_hex(32)
                status,body,_=request(public,'/controllers/careers-apply.php',fields,('cv.doc',doc,'application/msword'))
                check(status==200,'legacy DOC content accepted: '+body.decode()[:80])
                fields['request_key']=secrets.token_hex(32)
                check(request(public,'/controllers/careers-apply.php',fields,('last.pdf',pdf,'application/pdf'))[0]==200,'fifth application within rate limit accepted')
                fields['request_key']=secrets.token_hex(32)
                check(request(public,'/controllers/careers-apply.php',fields,('extra.pdf',pdf,'application/pdf'))[0]==429,'application rate limit enforced')
                php("$db->prepare('UPDATE staff SET status=? WHERE id=?')->execute(['Inactive',"+str(staff_id)+"]);")
                check(admin('jobs')[0]==403,'deactivated staff session denied')
                php("$db->prepare('UPDATE staff SET status=? WHERE id=?')->execute(['Active',"+str(staff_id)+"]);")
                php("$db->prepare('UPDATE career_jobs SET deadline=? WHERE id=?')->execute(['2000-01-01',"+str(job_id)+"]);")
                check(request(public,'/careers.php?job='+str(job_id))[0]==404,'expired job is not publicly available')
                admin('save_job',job)
            job["status"]="draft";admin("save_job",job)
            fields["request_key"]=secrets.token_hex(32)
            if not args.live_email:
                php("$db->prepare('UPDATE career_applications SET ip_hash=? WHERE job_id=?')->execute([str_repeat('0',64),"+str(job_id)+"]);")
                fields["email"]="closed-job@example.test"
            check(request(public,"/controllers/careers-apply.php",fields,("cv.pdf",pdf,"application/pdf"))[0]==422,"unpublished jobs reject application")
            check(request(public,"/careers.php?job="+str(job_id))[0]==404,"unpublished job details are not public")
            job["status"]="archived";admin("save_job",job)
            check(marker.encode() not in request(public,"/careers.php")[1],"archived job disappears from public listing")
            print("COMPLETE:",checks,"checks")
finally:
    if server and server.poll() is None:
        server.terminate()
        server.wait(timeout=10)
    # Delete only disposable fixture rows created by this invocation.
    for job_id in job_ids:
        php("$id="+str(int(job_id))+";$s=$db->prepare('SELECT title FROM career_jobs WHERE id=?');$s->execute([$id]);if(strpos((string)$s->fetchColumn(),'CareerTest-')!==0)exit(2);$db->prepare('DELETE FROM career_applications WHERE job_id=?')->execute([$id]);$db->prepare('DELETE FROM career_jobs WHERE id=?')->execute([$id]);")
    if staff_id:
        php("$db->prepare('DELETE FROM staff WHERE id=? AND email=?')->execute(["+str(staff_id)+",'"+staff_email+"']);")
