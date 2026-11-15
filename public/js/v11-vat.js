document.addEventListener('DOMContentLoaded',()=>{
 const buttons=[...document.querySelectorAll('[data-vat-display]')];
 if(!buttons.length)return;
 let mode='ex';try{mode=localStorage.getItem('wep-vat-display')==='inc'?'inc':'ex'}catch(e){}
 function refresh(){
  buttons.forEach(b=>b.setAttribute('aria-pressed',String(b.dataset.vatDisplay===mode)));
  document.querySelectorAll('.kite-tier-price[data-price-pence]').forEach(el=>{
    const p=Number(el.dataset.pricePence),rate=Number(el.dataset.vatBps||2000),units=Number(el.dataset.units||1);
    if(!Number.isFinite(p)||!Number.isFinite(rate))return;
    const unitMode=el.closest('.kite-order-section')?.querySelector('[data-price-mode="unit"].active')!==null;
    const displayed=mode==='inc'?Math.round(p*(10000+rate)/10000):p;
    const amount=unitMode ? displayed/Math.max(1,units) : displayed;
    if(unitMode && el.dataset.unitEnabled==='0'){el.textContent='—';return;}
    el.textContent=unitMode?amount.toFixed(1)+'p':'£'+(amount/100).toFixed(2);
  });
  document.querySelectorAll('[data-v11-money]').forEach(el=>{const p=Number(el.dataset.v11Money),rate=Number(el.dataset.v11Rate||0);if(Number.isFinite(p)&&Number.isFinite(rate))el.textContent='£'+((mode==='inc'?Math.round(p*(10000+rate)/10000):p)/100).toFixed(2);});
  document.querySelectorAll('[data-v11-vat-label]').forEach(e=>e.textContent=mode==='inc'?'Including VAT':'Excluding VAT');
 }
 buttons.forEach(b=>b.addEventListener('click',()=>{mode=b.dataset.vatDisplay;try{localStorage.setItem('wep-vat-display',mode)}catch(e){} refresh()}));
 // Existing matrix JS changes case/unit independently; run after it on click.
 document.querySelectorAll('[data-price-mode]').forEach(b=>b.addEventListener('click',()=>setTimeout(refresh,0)));
 refresh();
});
