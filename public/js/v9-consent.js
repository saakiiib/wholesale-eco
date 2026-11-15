(()=>{
 const KEY='wep-consent-v1';const get=()=>{try{return JSON.parse(localStorage.getItem(KEY)||'null')}catch{return null}};
 function save(value){localStorage.setItem(KEY,JSON.stringify({...value,saved_at:new Date().toISOString()}));document.querySelector('#v9-cookie-banner')?.remove();window.dispatchEvent(new CustomEvent('wep:consent-changed',{detail:value}));}
 function init(){const consent=get();const a=document.querySelector('#cookie-analytics'),m=document.querySelector('#cookie-marketing');if(a)a.checked=!!consent?.analytics;if(m)m.checked=!!consent?.marketing;
 document.querySelector('#cookie-save')?.addEventListener('click',()=>save({analytics:!!a?.checked,marketing:!!m?.checked}));
 if(consent||document.querySelector('#v9-cookie-banner'))return;
 const banner=document.createElement('div');banner.id='v9-cookie-banner';banner.setAttribute('role','dialog');banner.setAttribute('aria-label','Cookie choices');banner.innerHTML='<div><strong>Respecting your privacy</strong><p>Essential cookies keep our shop running. Optional cookies remain off until you choose. <a href="/cookies">Cookie policy & settings</a></p></div><div class="v9-cookie-buttons"><button type="button" data-choice="reject">Reject optional</button><button type="button" data-choice="accept">Accept all</button></div>';
 document.body.appendChild(banner);banner.querySelector('[data-choice="reject"]').onclick=()=>save({analytics:false,marketing:false});banner.querySelector('[data-choice="accept"]').onclick=()=>save({analytics:true,marketing:true});
 }if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
})();
