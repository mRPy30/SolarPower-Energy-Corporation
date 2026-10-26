/* Mobile navigation delegates to the existing sidebar handlers. */
(() => {
    function init() {
        const sidebar = document.querySelector('.sidebar');
        if (!sidebar) return;
        document.body.classList.add('staff-mobile-ui');
        const items = Array.from(sidebar.querySelectorAll('.menu-item'));
        const route = item => item.getAttribute('onclick')?.match(/showPage\('([^']+)'/)?.[1];
        const available = item => !item.hidden && getComputedStyle(item).display !== 'none';
        const header = document.createElement('header');
        header.className = 'staff-mobile-header';
        const logo = sidebar.querySelector('.logo img')?.cloneNode(true);
        if (logo) { logo.removeAttribute('style'); header.append(logo); }
        const heading = document.createElement('div');
        heading.className = 'staff-mobile-heading';
        heading.innerHTML = '<small>Staff workspace</small><h1></h1>';
        header.append(heading);
        const profile = document.createElement('button');
        profile.type = 'button'; profile.className = 'staff-mobile-profile';
        profile.setAttribute('aria-label', 'Account menu');
        profile.setAttribute('aria-expanded', 'false');
        profile.setAttribute('aria-controls', 'staff-account-menu');
        profile.innerHTML = '<i class="fas fa-user" aria-hidden="true"></i>';
        const avatar = document.querySelector('.staff-header-avatar img');
        if (avatar) { profile.replaceChildren(avatar.cloneNode(true)); }
        const account = document.createElement('div');
        account.id = 'staff-account-menu'; account.className = 'staff-account-menu'; account.hidden = true;
        const profileSource = items.find(item => route(item) === 'settings' && available(item));
        if (profileSource) {
            const action = document.createElement('button'); action.type = 'button';
            action.innerHTML = '<i class="fas fa-user" aria-hidden="true"></i> My Profile';
            action.addEventListener('click', () => { closeAccount(); profileSource.click(); });
            account.append(action);
        }
        const logoutSource = document.querySelector('a[href$="controllers/logout.php"]');
        if (logoutSource) {
            const logout = logoutSource.cloneNode(true); logout.removeAttribute('id'); account.append(logout);
        } else {
            // Standalone product editor shares the existing staff logout controller.
            const logout = document.createElement('a'); logout.href = '../../controllers/logout.php';
            logout.innerHTML = '<i class="fas fa-sign-out-alt" aria-hidden="true"></i> Logout'; account.append(logout);
        }
        function closeAccount() { account.hidden = true; profile.setAttribute('aria-expanded', 'false'); }
        profile.addEventListener('click', () => {
            account.hidden = !account.hidden; profile.setAttribute('aria-expanded', String(!account.hidden));
            if (!account.hidden) account.querySelector('button,a')?.focus();
        });
        header.append(account);
        document.addEventListener('click', event => { if (!account.contains(event.target) && !profile.contains(event.target)) closeAccount(); });
        header.addEventListener('keydown', event => { if (event.key === 'Escape' && !account.hidden) { closeAccount(); profile.focus(); } });
        header.append(profile);
        document.body.prepend(header);
        const drawer = document.createElement('dialog');
        drawer.className = 'staff-more'; drawer.id = 'staff-more-menu';
        drawer.setAttribute('aria-labelledby', 'staff-more-title');
        drawer.innerHTML = '<div class="staff-more-header"><h2 id="staff-more-title">All modules</h2><button type="button" aria-label="Close menu"><i class="fas fa-times" aria-hidden="true"></i></button></div><div class="staff-more-items"></div>';
        const drawerIdentity = document.createElement('div');
        const drawerLogo = logo?.cloneNode(true);
        if (drawerLogo) { drawerLogo.className='staff-drawer-brand'; drawerIdentity.append(drawerLogo); }
        const drawerTitle = drawer.querySelector('h2');
        drawerTitle.before(drawerIdentity); drawerIdentity.append(drawerTitle);
        document.body.append(drawer);
        let scrollPosition=0;
        let savedBodyStyle;
        function lockPage() {
            scrollPosition=window.scrollY;
            savedBodyStyle={position:document.body.style.position,top:document.body.style.top,width:document.body.style.width};
            document.body.style.position='fixed'; document.body.style.top=`-${scrollPosition}px`; document.body.style.width='100%';
        }
        function unlockPage() {
            if (!savedBodyStyle) return;
            Object.assign(document.body.style,savedBodyStyle); savedBodyStyle=null;
            window.scrollTo(0,scrollPosition);
        }
        drawer.querySelector('button').addEventListener('click', () => drawer.close());
        drawer.addEventListener('click', e => { if (e.target === drawer) drawer.close(); });
        const nav = document.createElement('nav'); nav.className = 'staff-bottom-nav'; nav.setAttribute('aria-label','Staff navigation');
        const primary = [['dashboard','Home','fa-house'],['inquiries','Inquiries','fa-envelope'],['product','Product','fa-cube'],['orders','Revenue','fa-chart-column']];
        primary.forEach(([id,label,icon]) => {
            const source = items.find(item => route(item) === id && available(item));
            if (!source) return;
            const button = document.createElement('button'); button.type='button'; button.dataset.page=id;
            button.innerHTML = `<i class="fas ${icon}" aria-hidden="true"></i><span>${label}</span>`;
            button.addEventListener('click', () => { source.click(); window.scrollTo({top:0,behavior:'instant'}); });
            nav.append(button);
        });
        const more = document.createElement('button'); more.type='button';
        more.innerHTML = '<i class="fas fa-bars" aria-hidden="true"></i><span>More</span>';
        more.setAttribute('aria-controls',drawer.id); more.setAttribute('aria-expanded','false');
        more.addEventListener('click', () => {
            const content = drawer.querySelector('.staff-more-items'); content.replaceChildren();
            sidebar.querySelectorAll('.menu-label,.menu-item').forEach(source => {
                if (!available(source)) return;
                if (source.classList.contains('menu-label')) {
                    const label=document.createElement('p'); label.className='staff-more-label'; label.textContent=source.textContent; content.append(label);
                } else {
                    const button=document.createElement('button'); button.type='button'; button.className='staff-more-link';
                    const icon=source.querySelector('i')?.cloneNode(true); if(icon) button.append(icon);
                    button.append(document.createTextNode(source.querySelector('span')?.textContent || source.textContent));
                    button.addEventListener('click',()=>{ drawer.close(); unlockPage(); source.click(); window.scrollTo(0,0); }); content.append(button);
                }
            });
                        const logout = account.querySelector('a[href$="controllers/logout.php"]');
            if (logout) { const link=logout.cloneNode(true); link.classList.add('staff-more-link'); content.append(link); }
            drawer.showModal(); lockPage(); more.setAttribute('aria-expanded','true');
        });
        drawer.addEventListener('close',()=>{ unlockPage(); more.setAttribute('aria-expanded','false'); more.focus(); });
        nav.append(more); document.body.append(nav);
        function sync() {
            heading.querySelector('h1').textContent = document.getElementById('page-title')?.textContent || document.querySelector('.header-left h1')?.textContent || 'Staff';
            const page = document.querySelector('.page-content.active')?.id || 'product';
            const current = page === 'add-product' ? 'product' : page;
            nav.querySelectorAll('[data-page]').forEach(button=>{
                if(button.dataset.page===current) button.setAttribute('aria-current','page'); else button.removeAttribute('aria-current');
            });
            if(!primary.some(([id])=>id===current)) more.setAttribute('aria-current','page'); else more.removeAttribute('aria-current');
        }
        const main=document.querySelector('.main-content');
        function adapt(root) {
            const tables = new Set(root.querySelectorAll('table'));
            const owner = root.closest?.('table');
            if (owner) tables.add(owner);
            tables.forEach(table=>{
                if (!table.closest('.staff-table-scroll')) {
                    const parent = table.parentElement;
                    const existing = /(?:table[-_](?:wrapper|wrap|container)|table-responsive)/.test(parent.className);
                    const wrapper = existing ? parent : document.createElement('div');
                    wrapper.classList.add('staff-table-scroll');
                    if (existing) wrapper.classList.add('staff-table-existing');
                    if (!wrapper.hasAttribute('tabindex')) wrapper.tabIndex=0;
                    if (!wrapper.hasAttribute('role')) wrapper.setAttribute('role','region');
                    if (!wrapper.hasAttribute('aria-label')) wrapper.setAttribute('aria-label','Scrollable data table');
                    if (!existing) { table.before(wrapper); wrapper.append(table); }
                }
                // Labels decorate the existing cells, so search, export and row
                // actions still operate on the original table and its data.
                if (table.matches('#clients .clients-table')) {
                    const labels = Array.from(table.querySelectorAll('thead th'), th=>th.textContent.trim());
                    table.querySelectorAll('tbody tr').forEach(row=>{
                        Array.from(row.cells).forEach((cell,index)=>{
                            if (cell.colSpan === 1 && labels[index]) cell.dataset.mobileLabel=labels[index];
                        });
                    });
                }
            });
            root.querySelectorAll('[style]').forEach(el=>{
                if(el.style.gridTemplateColumns) el.classList.add('staff-mobile-grid');
                if(el.style.display==='flex' && !el.closest('.preview-carousel')) el.classList.add('staff-mobile-flex');
            });
        }
        if(main) {
            adapt(main);
            new MutationObserver(records=>{
                records.forEach(record=>record.addedNodes.forEach(node=>{if(node.nodeType===1 && !node.classList.contains('staff-table-scroll')) adapt(node.parentElement || node);}));
                sync();
            }).observe(main,{subtree:true,childList:true});
            new MutationObserver(sync).observe(main,{subtree:true,attributes:true,attributeFilter:['class']});
        }
        matchMedia('(max-width:767px)').addEventListener('change',e=>{if(!e.matches) { if(drawer.open) drawer.close(); closeAccount(); }});
        sync();
    }
    if(document.readyState==='loading') document.addEventListener('DOMContentLoaded',init); else init();
})();
