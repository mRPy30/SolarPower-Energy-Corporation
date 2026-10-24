"""HTTP checks against disposable copies and the dedicated fixture server on 3319."""
import pathlib, tempfile, subprocess, socket, time, urllib.request, urllib.error, urllib.parse, http.cookiejar, json, shutil
ROOT=pathlib.Path(__file__).resolve().parents[1]
PHP=r'C:\xampp\php\php.exe'
MYSQL=r'C:\xampp\mysql\bin\mysql.exe'
def sql(query):
    subprocess.run([MYSQL,'--host=127.0.0.1','--port=3319','--user=root','solar_inventory_fixture','-e',query],check=True,capture_output=True)
sql("ALTER TABLE product ADD COLUMN IF NOT EXISTS warranty VARCHAR(100), ADD COLUMN IF NOT EXISTS description TEXT; UPDATE product SET stockQuantity=5 WHERE id=1; UPDATE product_brand_variants SET price=100 WHERE id=1;")
with tempfile.TemporaryDirectory(prefix='solar-inventory-http-') as work:
    work=pathlib.Path(work)
    for name in ['controllers/ordering/create-cod-order.php','controllers/cart.php','controllers/checkout-quote.php','add-to-cart.php','add-to-cart-ajax.php','includes/checkout-service.php','includes/checkout-integrity.php','includes/inventory.php','views/staff/edit_product.php']:
        target=work/name;target.parent.mkdir(parents=True,exist_ok=True);shutil.copyfile(ROOT/name,target)
    (work/'config').mkdir()
    (work/'config/dbconn.php').write_text("<?php mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);$conn=new mysqli('127.0.0.1','root','','solar_inventory_fixture',3319);")
    (work/'staff-fixture.php').write_text("<?php session_start();$_SESSION['user_id']=1;$_SESSION['role']='staff';echo '{}';")
    sock=socket.socket();sock.bind(('127.0.0.1',0));port=sock.getsockname()[1];sock.close()
    with (work/'server.log').open('wb') as log:
        server=subprocess.Popen([PHP,'-d','session.save_path='+str(work),'-S','127.0.0.1:'+str(port),'-t',str(work)],stdout=log,stderr=log,creationflags=getattr(subprocess,'CREATE_NO_WINDOW',0))
        try:
            client=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
            def request(path,data=None,form=False):
                headers={};body=None
                if data is not None:
                    body=(urllib.parse.urlencode(data) if form else json.dumps(data)).encode();headers['Content-Type']='application/x-www-form-urlencoded' if form else 'application/json'
                try:
                    with client.open(urllib.request.Request('http://127.0.0.1:'+str(port)+path,data=body,headers=headers),timeout=10) as r:return r.status,json.loads(r.read())
                except urllib.error.HTTPError as e:return e.code,json.loads(e.read())
            for _ in range(40):
                try:request('/controllers/cart.php');break
                except urllib.error.URLError:time.sleep(.1)
            checks=0
            def check(value,label):
                global checks
                assert value,label
                checks+=1;print('PASS',label)
            row={'product_id':1,'variant_id':1,'quantity':1,'price':.01}
            code,data=request('/add-to-cart-ajax.php',row)
            check(code==200 and data['cart'][0]['price']==100,'add endpoint replaces forged price')
            code,data=request('/controllers/cart.php?action=update',{'index':0,'quantity':6})
            check(code==409,'HTTP overstock rejected')
            check(request('/controllers/cart.php?action=update',{'index':0,'quantity':-1})[0]==409,'HTTP negative quantity rejected')
            sql('UPDATE product_brand_variants SET price=125 WHERE id=1')
            code,data=request('/controllers/cart.php?action=get')
            check(data['cart'][0]['price']==125 and data['price_changed'],'cart refresh signals price change')
            code,quote=request('/controllers/checkout-quote.php',{'items':[row],'delivery_rate_id':1,'calculated_delivery_fee':0})
            check(code==200 and quote['total']==145 and bool(quote['quote_token']),'quote returns current total and confirmation token')
            cod={'customerName':'Fixture','customerEmail':'fixture@example.test','customerPhone':'09000000000','customerAddress':'Test address','delivery_rate_id':1,'totalAmount':145,'items':[dict(row,price=125)]}
            code,order=request('/controllers/ordering/create-cod-order.php',cod)
            check(code==200 and order.get('success'),'COD endpoint saves validated order')
            code,again=request('/controllers/ordering/create-cod-order.php',cod)
            check(again.get('success') and again['orderRef']==order['orderRef'],'COD request retry returns same order')
            bad=dict(cod,items=[dict(row,price=1)])
            check(not request('/controllers/ordering/create-cod-order.php',bad)[1]['success'],'COD crafted price rejected before order creation')
            form={'product_id':2,'displayName':'Fixture','brandName':'Brand','category':'Misc','price':'75','stockQuantity':'3','status':'Active','description':'Fixture','warranty':'1 year'}
            check(request('/views/staff/edit_product.php',form,True)[0]==403,'admin save denies anonymous request')
            request('/staff-fixture.php')
            check(request('/views/staff/edit_product.php',form,True)[1]['success'],'staff saves stock and price')
            code,data=request('/controllers/cart.php?action=buynow',{'item':{'id':2,'quantity':1,'price':1}})
            check(code==200 and data['cart'][0]['stockQuantity']==3 and data['cart'][0]['price']==75,'saved admin stock and price reach cart')
            form['stockQuantity']='0';check(request('/views/staff/edit_product.php',form,True)[1]['success'],'admin zero stock saved')
            check(request('/controllers/cart.php?action=get')[0]==409,'zero stock blocks existing cart')
            form['stockQuantity']='-1';check(not request('/views/staff/edit_product.php',form,True)[1]['success'],'admin negative stock rejected')
            form['stockQuantity']='3';form['price']='-1';check(not request('/views/staff/edit_product.php',form,True)[1]['success'],'admin negative price rejected')
            form['price']='75';form['status']='Hidden';check(request('/views/staff/edit_product.php',form,True)[1]['success'],'admin hide saved')
            check(request('/controllers/cart.php?action=get')[0]==409,'hidden product blocked')
            check(request('/controllers/cart.php?action=remove',{'index':0})[1]['success'],'unavailable item can be removed')
            print('TOTAL',checks,'HTTP checks passed. No real payments or emails sent.')
        finally:
            server.terminate();server.wait(timeout=10)
