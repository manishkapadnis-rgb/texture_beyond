// Reveal Animations
const observer = new IntersectionObserver((entries)=>{
  entries.forEach(en=>{ if(en.isIntersecting) en.target.classList.add('active'); });
},{threshold:0.1, rootMargin:'0px 0px -50px 0px'});
document.querySelectorAll('.reveal-up').forEach(el=>observer.observe(el));

// GSAP scroll animations
if (window.gsap && window.ScrollTrigger){
  gsap.registerPlugin(ScrollTrigger);

  gsap.utils.toArray('.gsap-fade').forEach(el=>{
    gsap.from(el, {opacity:0, y:40, duration:1.2, ease:'power3.out',
      scrollTrigger:{trigger:el, start:'top 85%'}});
  });

  gsap.utils.toArray('.gsap-stagger > *').forEach((el,i)=>{
    gsap.from(el, {opacity:0, y:50, duration:1, delay:i*0.1, ease:'power3.out',
      scrollTrigger:{trigger:el, start:'top 88%'}});
  });

  // Section header reveal — handled by .gsap-fade above. Slider has own animation.
}

// Flash auto-hide
setTimeout(()=>{ document.querySelectorAll('.flash-msg').forEach(f=>f.style.display='none'); }, 4000);

// AJAX add to cart helper
window.addToCart = function(pid, qty=1){
  fetch(SITE_URL + '/cart-action.php', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:`action=add&id=${pid}&qty=${qty}`})
    .then(r=>r.json()).then(d=>{
      if (d.ok){
        showToast('Added to cart');
        const badge = document.querySelector('a[href*="cart.php"] span');
        if (badge) badge.textContent = d.count;
        else location.reload();
      } else showToast(d.msg || 'Error', true);
    });
};
function showToast(msg, err=false){
  const t = document.createElement('div');
  t.className = 'flash-msg ' + (err ? 'error' : 'success');
  t.textContent = msg;
  document.body.appendChild(t);
  setTimeout(()=>t.remove(), 3000);
}
