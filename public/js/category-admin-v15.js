document.addEventListener('DOMContentLoaded',()=>{
 const dialog=document.getElementById('we-create-category-modal');
 const open=document.getElementById('we-add-category-button');
 if(!dialog||!open)return;
 open.addEventListener('click',()=>dialog.showModal());
 dialog.querySelectorAll('.we-category-modal-close,.we-category-modal-cancel').forEach(button=>button.addEventListener('click',()=>dialog.close()));
 dialog.addEventListener('click',event=>{if(event.target===dialog)dialog.close()});
 const name=document.getElementById('we-category-name'),slug=document.getElementById('we-category-slug');
 if(name&&slug){name.addEventListener('input',()=>{if(slug.dataset.edited==='true')return;slug.value=name.value.toLowerCase().trim().replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'')});slug.addEventListener('input',()=>{slug.dataset.edited='true'})}
});
