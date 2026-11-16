document.addEventListener('click', function(event) {
  const toggle=event.target.closest('[data-category-toggle]');
  if (!toggle) return;
  const target=document.getElementById(toggle.getAttribute('aria-controls'));
  if (!target) return;
  const expanded=toggle.getAttribute('aria-expanded')==='true';
  toggle.setAttribute('aria-expanded', String(!expanded));
  target.hidden=expanded;
});
