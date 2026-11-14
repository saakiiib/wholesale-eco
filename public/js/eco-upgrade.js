document.addEventListener('DOMContentLoaded',()=>{
 const toast=document.querySelector('[data-basket-toast]');
 if(toast){toast.querySelector('[data-close-toast]')?.addEventListener('click',()=>toast.classList.add('is-hidden'));}
 document.querySelectorAll('[data-gallery-src]').forEach(button=>button.addEventListener('click',()=>{const image=document.getElementById('gallery-active-img');if(!image)return;image.src=button.dataset.gallerySrc;image.alt=button.dataset.galleryAlt||'';document.querySelectorAll('[data-gallery-src]').forEach(b=>b.classList.remove('is-selected'));button.classList.add('is-selected');}));
});
