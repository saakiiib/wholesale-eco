document.addEventListener('DOMContentLoaded',()=>{
 const search=document.getElementById('kite-variant-search');
 const rows=[...document.querySelectorAll('.kite-variant-row')];
 const empty=document.getElementById('kite-no-results');
 const filter=()=>{const term=(search?.value||'').trim().toLowerCase();let visible=0;rows.forEach(row=>{const ok=String(row.dataset.variantSearch||'').includes(term);row.hidden=!ok;if(ok)visible++;});if(empty)empty.hidden=visible>0||rows.length===0;};
 search?.addEventListener('input',filter);
 const clear=()=>{if(search){search.value='';filter();search.focus();}};
 document.getElementById('kite-clear-filters')?.addEventListener('click',clear);
 document.getElementById('kite-reset-inline')?.addEventListener('click',clear);
 const gbp=new Intl.NumberFormat('en-GB',{style:'currency',currency:'GBP'});
 const pence=(p)=>Math.round(p*10)/10;
 document.querySelectorAll('[data-price-mode]').forEach(button=>button.addEventListener('click',()=>{
   const mode=button.dataset.priceMode;
   document.querySelectorAll('[data-price-mode]').forEach(b=>{b.classList.toggle('active',b===button);b.setAttribute('aria-pressed',String(b===button));});
   document.querySelectorAll('.kite-tier-price').forEach(el=>{const price=Number(el.dataset.pricePence);const units=Math.max(1,Number(el.dataset.units));el.textContent=mode==='case'?gbp.format(price/100):(pence(price/units).toFixed(1)+'p');});
   document.querySelectorAll('.kite-price-unit-title').forEach(el=>el.textContent=mode==='case'?'per case':'per unit');
 }));
 document.querySelectorAll('.kite-stepper').forEach(stepper=>{const input=stepper.querySelector('input'); const adjust=(delta)=>{const min=Number(input.min)||1;const max=Number(input.max)||10000;input.value=String(Math.min(max,Math.max(min,(Number(input.value)||min)+delta)));};stepper.querySelector('.kite-step-up')?.addEventListener('click',()=>adjust(1));stepper.querySelector('.kite-step-down')?.addEventListener('click',()=>adjust(-1));});
});

// Gallery thumbnails are shared by product and category range pages.
document.addEventListener('DOMContentLoaded',()=>{
 const main=document.getElementById('gallery-active-img');
 if(!main)return;
 document.querySelectorAll('[data-gallery-src]').forEach(button=>button.addEventListener('click',()=>{
   main.src=button.dataset.gallerySrc;
   main.alt=button.dataset.galleryAlt||'Product image';
   document.querySelectorAll('[data-gallery-src]').forEach(b=>b.classList.toggle('is-selected',b===button));
 }));
});
