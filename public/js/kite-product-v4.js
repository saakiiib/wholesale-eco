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
 document.querySelectorAll('.kite-stepper').forEach(stepper=>{const input=stepper.querySelector('input'); const adjust=(delta)=>{const min=Number(input.min)||1;const max=Number(input.max)||10000;input.value=String(Math.min(max,Math.max(min,(Number(input.value)||min)+delta)));input.dispatchEvent(new Event('input',{bubbles:true}));};stepper.querySelector('.kite-step-up')?.addEventListener('click',()=>adjust(1));stepper.querySelector('.kite-step-down')?.addEventListener('click',()=>adjust(-1));});
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

// V8: highlight only the active volume tier of each SKU, based on its selected pack quantity.
// Optional pallet ordering converts quantity to packs before server-side checkout pricing.
document.addEventListener('DOMContentLoaded',()=>{
 const gbp = new Intl.NumberFormat('en-GB',{style:'currency',currency:'GBP'});
 const rows=[...document.querySelectorAll('.kite-variant-row')];
 const activeTier=packs=>packs>=20?3:packs>=10?2:packs>=4?1:0;
 const updateRow=(row)=>{
  const form=row.querySelector('.kite-quick-add');if(!form)return;
  const input=form.querySelector('input[name="packs"]');if(!input)return;
  const perPallet=Math.max(0,Number(row.dataset.packsPerPallet)||0);
  const kind=form.querySelector('.we-order-unit')?.value||'pack';
  const factor=kind==='pallet' && row.dataset.allowPallet==='1'?perPallet:1;
  const raw=Math.min(Number(input.max)||10000,Math.max(1,Number(input.value)||1));
  const ordered=raw*Math.max(1,factor);
  const tier=activeTier(ordered);
  row.querySelectorAll('[data-tier-index]').forEach(el=>{
   const selected=Number(el.dataset.tierIndex)===tier;
   el.classList.toggle('kite-tier-selected',selected);
   el.setAttribute('aria-label',selected?'Current quantity price tier':'Other quantity price tier');
  });
  const unitField=form.querySelector('input[name="order_unit"]');if(unitField)unitField.value=kind;
  const unitDisplay=form.querySelector('.we-order-unit');
  // Quantity selection is a quote preview. Never silently force out-of-stock products back to 1,
  // or customers cannot see the higher price tiers. Server-side cart validation remains authoritative.
  const actualStock=Math.max(0,Number(input.dataset.stockPacks)||0);
  const purchasable=actualStock>=ordered && ordered<=10000 && actualStock>0;
  const addButton=form.querySelector('.kite-add-button');
  if(addButton){addButton.disabled=!purchasable;addButton.title=purchasable?'Add to basket':'Requested quantity exceeds available stock';}
  row.classList.toggle('kite-out-of-stock-request',!purchasable);

  // Display the *actual* price per pack for the selected quantity, using the existing calculated tier prices.
  const activePrice=row.querySelector('[data-tier-index="'+tier+'"] .kite-tier-price');
  let hint=row.querySelector('.we-line-preview');
  if(!hint){hint=document.createElement('small');hint.className='we-line-preview';form.appendChild(hint);}
  const packPence=Number(activePrice?.dataset.pricePence)||0;
  hint.textContent=gbp.format(packPence*ordered/100)+' ex VAT · '+ordered+' packs'+(!purchasable?' · Not available for ordering':'');
 };
 rows.forEach(row=>{
  const input=row.querySelector('input[name="packs"]');
  if(!input)return;
  input.dataset.stockPacks=input.getAttribute('data-stock-packs')||'0';
  input.addEventListener('input',()=>updateRow(row));
  input.addEventListener('change',()=>updateRow(row));

  row.querySelector('.we-order-unit')?.addEventListener('change',()=>{input.value='1';updateRow(row);});
  updateRow(row);
 });
 document.querySelectorAll('[data-unit-mode]').forEach(btn=>btn.addEventListener('click',()=>{
  const mode=btn.dataset.unitMode;
  document.querySelectorAll('[data-unit-mode]').forEach(b=>{b.classList.toggle('active',b===btn);b.setAttribute('aria-pressed',String(b===btn));});
  document.querySelectorAll('.we-unit-pack').forEach(el=>el.hidden=mode!=='pack');
  document.querySelectorAll('.we-unit-pallet').forEach(el=>el.hidden=mode!=='pallet');
  document.querySelectorAll('.we-units-col-title').forEach(el=>el.textContent='Units per '+mode);
 }));
 // Unit prices are a display preference controlled by SKU. Disabled items show a neutral dash.
 document.querySelectorAll('[data-price-mode]').forEach(btn=>btn.addEventListener('click',()=>{
  if(btn.dataset.priceMode==='unit')document.querySelectorAll('.kite-tier-price[data-unit-enabled="0"]').forEach(el=>el.textContent='—');
 }));
});
