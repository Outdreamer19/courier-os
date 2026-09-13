<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>TODAY Shipping &amp; Logistics — Connecting Jamaica to the World</title>
<meta name="description" content="Shop from your favourite US stores. TODAY Shipping handles everything from shipping to customs so your purchases arrive safely in Jamaica.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=Inter:wght@400;450;500;600&display=swap" rel="stylesheet">
<style>
/* ============================================================
   TODAY SHIPPING — Agero-style rebuild
   Cream canvas with navy / red section bands · big bold type · rounded cards
   Accent: brand red · Dark sections: brand navy
   ============================================================ */
:root{
  --cream:     #EFEDEA;   /* main canvas */
  --cream-2:   #E4E2DD;   /* recessed sections */
  --cream-3:   #F6F5F2;   /* cards */
  --ink:       #191919;   /* near-black text */
  --ink-soft:  #6E6E6A;   /* muted text */
  --navy:      #12275E;
  --navy-deep: #0B1836;
  --navy-ink:  #060F26;
  --red:       #D0202E;
  --red-2:     #E5535E;
  --navy-mist: #A3B8E8;   /* process band */
  --red-wash:  #EDA6AE;   /* rates band */
  --line:      rgba(25,25,25,.12);

  --font-display:'Sora', -apple-system, sans-serif;
  --font-body:   'Inter', -apple-system, sans-serif;
  --ease:        cubic-bezier(.22,.61,.36,1);
  --r-lg: 40px;
  --r-md: 24px;
  --r-sm: 16px;
}
*{margin:0;padding:0;box-sizing:border-box}
html{scroll-behavior:smooth; overflow-x:hidden; max-width:100%}
body{
  font-family:var(--font-body);
  background:var(--navy-mist);
  color:var(--ink);
  overflow-x:hidden;
  max-width:100%;
  -webkit-font-smoothing:antialiased;
  text-rendering:optimizeLegibility;
}
::selection{background:var(--red);color:#fff}
a{color:inherit}
img{display:block;max-width:100%}

/* small caps label used across sections e.g. (Rates) */
.eyebrow{
  font-family:var(--font-body); font-weight:500; font-size:.9rem;
  color:var(--ink-soft); letter-spacing:.01em;
}

.reveal{opacity:0; transform:translateY(34px)}
.reveal-l{opacity:0; transform:translateX(-72px)}
.reveal-r{opacity:0; transform:translateX(72px)}
.load{opacity:0}
.head-split{opacity:0}

/* ============ TOP AVAILABILITY BADGE ============ */
/* Single continuous SVG path (Agero profile): rounded top, flat bottom, sides bow
   inward — no downward tab, no pseudo-element circles. */
.availbar{
  position:absolute; top:0; left:50%; transform:translateX(-50%);
  z-index:60;
  width:clamp(240px, 46vw, 342px);
  color:var(--navy-ink);
  pointer-events:none;
}
.availbar-shape{
  display:block; width:100%; height:auto;
  filter:drop-shadow(0 6px 18px rgba(6,15,38,.22));
}
.availbar-row{
  position:absolute; inset:0;
  display:flex; align-items:center; justify-content:center; gap:.5rem;
  padding:0 1.25rem;
  color:#fff; font-size:clamp(.68rem, 1.4vw, .74rem); font-weight:500;
  letter-spacing:.02em; white-space:nowrap;
  pointer-events:auto;
}
.availbar .dot{width:7px;height:7px;border-radius:50%;background:#4ED17A;box-shadow:0 0 0 3px rgba(78,209,122,.22);flex:none}

/* ============ NAV ============ */
nav{
  position:fixed; top:0; left:0; right:0; z-index:50;
  display:flex; align-items:center; justify-content:space-between;
  padding:2.3rem 4vw 1rem;
  transition:padding .4s var(--ease);
}
nav.solid{
  padding-top:.7rem; padding-bottom:.7rem;
  background:rgba(239,237,234,.82); backdrop-filter:blur(16px) saturate(1.2);
  box-shadow:0 1px 0 var(--line);
}
.brand{display:flex; align-items:center; gap:.6rem; text-decoration:none}
.brand .mark{
  width:34px;height:34px;border-radius:10px;flex:none;
  background:linear-gradient(150deg,var(--navy),var(--navy-ink));
  display:grid;place-items:center;box-shadow:0 6px 16px rgba(18,39,94,.28)}
.brand .mark svg{width:18px;height:18px}
.brand .wm{font-family:var(--font-display); font-weight:800; font-size:1.4rem; letter-spacing:-.01em; line-height:1; color:var(--ink)}
.brand .wm i{color:var(--red); font-style:normal}
.brand-name{font-family:var(--font-display); font-weight:800; font-size:1.15rem; letter-spacing:.01em; line-height:1; color:var(--navy); white-space:nowrap}
.brand-name i{color:var(--ink-soft); font-style:normal; font-weight:600}
footer .brand-name{font-size:1.3rem}
.brand-logo{height:46px; width:auto; display:block; border-radius:50%}
footer .brand-logo{height:58px}
.nav-mid{display:flex; gap:2.1rem; align-items:center; font-size:.95rem; font-weight:450}
.nav-mid a{position:relative; text-decoration:none; color:var(--ink); opacity:.85; transition:opacity .2s}
.nav-mid a::after{content:''; position:absolute; left:0; right:0; bottom:-6px; height:2px; border-radius:2px;
  background:var(--red); transform:scaleX(0); transform-origin:left; transition:transform .28s var(--ease)}
.nav-mid a:hover{opacity:1}
.nav-mid a:hover::after{transform:scaleX(1)}
.nav-right{display:flex; align-items:center; gap:.55rem}
.pill{
  font-family:var(--font-body); font-weight:500; font-size:.95rem;
  padding:.72rem 1.5rem; border-radius:99px; text-decoration:none;
  display:inline-flex; align-items:center; gap:.5rem; cursor:pointer; border:none;
  transition:transform .3s var(--ease), box-shadow .3s, background .3s, color .3s, border-color .3s;
}
.pill svg{transition:transform .3s var(--ease)}
.pill:hover svg{transform:translateX(3px)}
.pill-dark{background:var(--navy-ink); color:#fff; box-shadow:0 10px 26px rgba(6,15,38,.28)}
.pill-dark:hover{transform:translateY(-2px); box-shadow:0 16px 34px rgba(6,15,38,.34)}
#nav .pill-dark{background:var(--navy); box-shadow:0 10px 26px rgba(18,39,94,.34)}
#nav .pill-dark:hover{box-shadow:0 16px 34px rgba(18,39,94,.42)}
.pill-outline{
  background:#fff; color:var(--navy); border:1.5px solid rgba(18,39,94,.28);
  box-shadow:0 6px 16px rgba(18,39,94,.08); font-weight:600;
}
.pill-outline:hover{
  transform:translateY(-2px); border-color:var(--navy); color:var(--navy);
  box-shadow:0 12px 24px rgba(18,39,94,.14);
}
#nav .pill-outline{background:rgba(255,255,255,.92)}
.pill-red{background:var(--red); color:#fff; box-shadow:0 10px 26px rgba(208,32,46,.32)}
.pill-red:hover{transform:translateY(-2px); box-shadow:0 16px 34px rgba(208,32,46,.42)}
.pill-light{background:#fff; color:var(--ink); box-shadow:0 8px 22px rgba(6,15,38,.12)}
.pill-light:hover{transform:translateY(-2px)}
@media(max-width:900px){
  .nav-mid{display:none}
  .nav-register{display:none}
  /* On small screens Log in takes the primary CTA slot Create Account occupied. */
  #nav .nav-login{
    background:var(--navy); color:#fff; border:none;
    box-shadow:0 10px 26px rgba(18,39,94,.34); font-weight:500;
    padding:.65rem 1.25rem; font-size:.9rem;
  }
  #nav .nav-login:hover{box-shadow:0 16px 34px rgba(18,39,94,.42)}
}

/* ============ HERO ============ */
/* wrapper carries the drop shadow so the mask on .hero can't clip it */
.hero-wrap{
  position:relative; z-index:2;
  border-radius:0 0 var(--r-lg) var(--r-lg);
  box-shadow:0 40px 80px -60px rgba(6,15,38,.4);
}
.hero{
  position:relative; z-index:2;
  overflow:hidden;
  background:
    radial-gradient(95% 55% at 50% -6%, rgba(18,39,94,.52) 0%, transparent 62%),
    radial-gradient(52% 48% at 62% 80%, rgba(208,32,46,.36) 0%, transparent 72%),
    linear-gradient(180deg, #D5DEF4 0%, var(--cream) 42%);
  border-radius:0 0 var(--r-lg) var(--r-lg);
  padding:11rem 6vw 6rem;
  min-height:92vh;
  display:flex; flex-direction:column; align-items:center; justify-content:center;
  text-align:center;
}
.hero h1{
  font-family:var(--font-display); font-weight:800;
  font-size:clamp(1.9rem,4.8vw,3.55rem); line-height:1.15; letter-spacing:-.03em;
  max-width:16em; color:var(--ink); opacity:0;
}
.hero h1 .word{padding-bottom:.02em}
.hero h1 .g{color:var(--ink-soft)}
.hero h1 .r{color:var(--red)}
.hero h1 .chip{
  display:inline-grid; place-items:center; vertical-align:middle;
  width:.9em; height:.9em; border-radius:50%; margin:0 .16em;
  transform:translateY(-.04em);
  background:linear-gradient(150deg,var(--navy),var(--navy-ink));
  box-shadow:0 10px 26px rgba(18,39,94,.3), inset 0 0 0 4px rgba(255,255,255,.9);
}
.hero h1 .chip.red{background:linear-gradient(150deg,var(--red-2),var(--red))}
.hero h1 .chip svg{width:.5em;height:.5em;stroke:#fff}

/* idle animation — chips gently float + their icons come alive */
@keyframes chipFloat{
  0%,100%{transform:translateY(-.04em) rotate(0deg)}
  50%{transform:translateY(-.30em) rotate(-7deg)}
}
@keyframes chipGlow{
  0%,100%{box-shadow:0 10px 26px rgba(208,32,46,.30), inset 0 0 0 4px rgba(255,255,255,.9)}
  50%{box-shadow:0 16px 34px rgba(208,32,46,.55), inset 0 0 0 4px rgba(255,255,255,.9)}
}
@keyframes chipPop{0%,100%{transform:scale(1)}50%{transform:scale(1.18)}}
.hero h1 .chip{animation:chipFloat 3.4s var(--ease) infinite}
.hero h1 .chip.red{animation:chipFloat 4s var(--ease) -1.3s infinite, chipGlow 2.8s ease-in-out infinite}
.hero h1 .chip.red svg{animation:chipPop 2.8s var(--ease) infinite}
.hero-emoji{
  display:inline-block; vertical-align:middle; font-style:normal; line-height:1;
  font-size:.85em; margin:0 .04em; transform:translateY(-.04em);
  animation:chipFloat 3.4s var(--ease) infinite;
}

/* flight overlay — a plane takes off from the box and lands at the door */
.hero-flight{position:absolute; inset:0; width:100%; height:100%; overflow:visible; pointer-events:none; z-index:3}
.flight-trail{fill:none; stroke:var(--red); stroke-width:5; stroke-linecap:butt; stroke-dasharray:14 10; opacity:.85}
.flight-reveal{fill:none; stroke:#fff; stroke-width:26; stroke-linecap:round}
.flight-plane{opacity:0}
.flight-plane path{fill:var(--red)}
.hero p.lead{
  margin-top:1.8rem; font-size:clamp(1rem,1.5vw,1.2rem); line-height:1.6;
  color:var(--ink-soft); max-width:52ch; font-weight:450;
}
.hero .cta{margin-top:2.6rem; display:flex; gap:.8rem; flex-wrap:wrap; justify-content:center}
.hero-mini{
  margin-top:3.2rem; display:flex; gap:2rem; flex-wrap:wrap; justify-content:center;
  font-size:.86rem; color:var(--ink-soft);
}
.hero-mini span{display:inline-flex; align-items:center; gap:.5rem}
.hero-mini svg{width:16px;height:16px;stroke:var(--red);fill:none}

/* ============ generic section ============ */
.section{max-width:1180px; margin:0 auto; padding:6.5rem 5vw}
.sec-head{display:flex; align-items:baseline; gap:.6rem; margin-bottom:.4rem}
.section h2{
  font-family:var(--font-display); font-weight:700;
  font-size:clamp(2rem,4.6vw,3.4rem); line-height:1.05; letter-spacing:-.025em; color:var(--ink)}
.section h2 .r{color:var(--red)}
.section .sub{margin-top:1rem; color:var(--ink-soft); font-size:1.05rem; line-height:1.6; max-width:56ch}

/* ============ PROCESS (services split rows) ============ */
#process{
  position:relative;
  overflow-x:hidden;
  --ink-soft: #3E4458;
  background:
    radial-gradient(90% 50% at 0% 0%, rgba(18,39,94,.28), transparent 55%),
    linear-gradient(180deg, #8FA8DC 0%, var(--navy-mist) 30%);
}
#process::after{
  content:'';
  position:absolute; left:0; right:0; bottom:0; height:5.5rem; z-index:0;
  background:linear-gradient(180deg, transparent, var(--red-wash));
  pointer-events:none;
}
.proc-wrap{position:relative; z-index:1; max-width:1180px; margin:0 auto; padding:5.5rem 5vw 6.5rem}
.proc-row{
  display:grid; grid-template-columns:.9fr 1.55fr .95fr; gap:2.5rem; align-items:center;
  padding:3.2rem 1.9rem; margin:0 -1.9rem; position:relative; border-radius:22px;
  border-top:1px solid var(--line);
  transition:transform .45s var(--ease), background .45s var(--ease), box-shadow .45s var(--ease), border-color .3s;
}
/* hover pop-out — the whole step lifts and turns white to stand out */
.proc-row:hover{
  transform:translateY(-6px); background:#fff; border-color:transparent; z-index:2;
  box-shadow:0 42px 72px -30px rgba(6,15,38,.45);
}
.proc-vis{transition:transform .4s var(--ease), box-shadow .4s var(--ease)}
.proc-row:first-of-type{border-top:none}
.proc-name{font-family:var(--font-display); font-weight:600; font-size:1.6rem; letter-spacing:-.02em; color:var(--navy)}
.proc-tags{margin-top:1rem; display:flex; flex-wrap:wrap; gap:.5rem}
.tag{font-size:.8rem; font-weight:500; padding:.4rem .8rem; border-radius:99px; background:#fff; color:var(--ink); box-shadow:0 4px 14px rgba(6,15,38,.06)}
.tag .d{display:inline-block;width:6px;height:6px;border-radius:50%;margin-right:.45rem;vertical-align:middle}
/* visual mockup card */
.proc-vis{
  border-radius:var(--r-md); aspect-ratio:16/10; overflow:hidden; position:relative;
  background:linear-gradient(160deg,#182a5c,#0a1430);
  box-shadow:0 30px 60px -24px rgba(6,15,38,.5);
  display:grid; place-items:center;
}
.proc-vis .glyph{width:34%; opacity:.95}
.proc-vis .glyph svg{width:100%;height:auto;stroke:#fff;stroke-width:1.4;fill:none;stroke-linecap:round;stroke-linejoin:round}
.proc-vis.warm{background:linear-gradient(160deg,#7a1620,var(--red))}
.proc-vis.photo{background:#0a1430}
.proc-vis.photo img{position:absolute; inset:0; width:100%; height:100%; object-fit:cover; display:block}
.proc-vis.photo::after{content:''; position:absolute; inset:0; z-index:1; pointer-events:none;
  background:linear-gradient(180deg,rgba(6,15,38,.45) 0%,rgba(6,15,38,.05) 42%,rgba(6,15,38,0) 100%)}
.proc-vis .badge{z-index:2}
.proc-vis .grid-bg{position:absolute; inset:0; opacity:.16;
  background-image:linear-gradient(rgba(255,255,255,.4) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.4) 1px,transparent 1px);
  background-size:34px 34px}
.proc-vis .badge{position:absolute; top:14px; left:16px; font-family:var(--font-body); font-weight:600; font-size:.7rem; letter-spacing:.16em; color:rgba(255,255,255,.8)}
.proc-detail p{color:var(--ink-soft); font-size:1rem; line-height:1.6}
.proc-meta{margin-top:1.6rem}
.proc-meta .mrow{display:flex; justify-content:space-between; padding:.75rem 0; border-top:1px solid var(--line); font-size:.92rem}
.proc-meta .mrow span:first-child{color:var(--ink-soft)}
.proc-meta .mrow span:last-child{font-weight:600; color:var(--ink)}
@media(max-width:860px){
  .proc-wrap{padding-left:1.25rem; padding-right:1.25rem}
  .proc-row{grid-template-columns:1fr; gap:1.4rem; padding:1.8rem 1.2rem 2rem; margin:0}
  .proc-vis{order:-1; width:100%; max-width:100%}
  /* Horizontal slide-ins overflow the viewport and make iOS shrink the page. */
  .reveal-l{transform:translateX(-16px)}
  .reveal-r{transform:translateX(16px)}
}

/* ============ RATES / PRICING ============ */
#rates{
  position:relative;
  --ink-soft: #4A3034;
  background:
    radial-gradient(90% 55% at 100% 0%, rgba(208,32,46,.42), transparent 62%),
    radial-gradient(70% 40% at 0% 80%, rgba(208,32,46,.22), transparent 55%),
    linear-gradient(180deg, #E89098 0%, var(--red-wash) 32%);
}
#rates::after{
  content:'';
  position:absolute; left:0; right:0; bottom:0; height:4.5rem; z-index:0;
  background:linear-gradient(180deg, transparent, var(--cream));
  pointer-events:none;
}
#rates .section{position:relative; z-index:1}
.plan-stack{margin-top:3rem; display:flex; flex-direction:column; gap:1.4rem}
.plan{
  border-radius:var(--r-lg); padding:2.6rem;
  display:grid; grid-template-columns:1fr 1fr; gap:2rem;
  position:sticky; top:6rem;
}
.plan.light{background:var(--cream-3); box-shadow:0 30px 60px -34px rgba(6,15,38,.3), inset 0 0 0 1px var(--line)}
.plan.dark{
  color:#fff;
  background:radial-gradient(120% 140% at 20% 120%, var(--red) 0%, #6d121b 42%, #180a0c 100%);
  box-shadow:0 40px 80px -34px rgba(120,20,28,.6);
}
.plan .p-ic{width:48px;height:48px;border-radius:14px;display:grid;place-items:center;background:var(--navy-ink); color:#fff; margin-bottom:1.6rem}
.plan.dark .p-ic{background:rgba(255,255,255,.14)}
.plan .p-ic svg{width:22px;height:22px;stroke:#fff;fill:none;stroke-width:1.8;stroke-linejoin:round;stroke-linecap:round}
.plan h3{font-family:var(--font-display); font-weight:700; font-size:1.7rem; letter-spacing:-.02em; margin-bottom:.6rem}
.plan .p-desc{font-size:.98rem; line-height:1.6; color:var(--ink-soft); max-width:34ch}
.plan.dark .p-desc{color:rgba(255,255,255,.72)}
.plan .p-meta{margin-top:1.8rem; display:flex; justify-content:space-between; font-size:.9rem; border-top:1px solid var(--line); padding-top:1rem}
.plan.dark .p-meta{border-color:rgba(255,255,255,.18)}
.plan .p-meta span:first-child{color:var(--ink-soft)} .plan.dark .p-meta span:first-child{color:rgba(255,255,255,.6)}
.plan .price{font-family:var(--font-display); font-weight:800; font-size:3rem; letter-spacing:-.03em; line-height:1}
.plan.dark .price .accent{color:var(--red-2)}
.plan .price small{font-family:var(--font-body); font-weight:500; font-size:1rem; color:var(--ink-soft); letter-spacing:0}
.plan.dark .price small{color:rgba(255,255,255,.6)}
.plan .p-feats{margin-top:1.6rem; list-style:none; display:flex; flex-direction:column; gap:.9rem; border-top:1px solid var(--line); padding-top:1.6rem}
.plan.dark .p-feats{border-color:rgba(255,255,255,.18)}
.plan .p-feats li{display:flex; align-items:flex-start; gap:.7rem; font-size:.95rem}
.plan .p-feats .ck{width:20px;height:20px;border-radius:50%;flex:none;display:grid;place-items:center;background:var(--navy-ink)}
.plan.dark .p-feats .ck{background:rgba(255,255,255,.16)}
.plan .p-feats .ck svg{width:11px;height:11px;stroke:#fff;stroke-width:3.2;fill:none;stroke-linecap:round;stroke-linejoin:round}
.plan .p-right{display:flex; flex-direction:column}
.plan .p-feats + .pill{margin-top:1.8rem; width:100%; justify-content:center}
@media(max-width:820px){.plan{grid-template-columns:1fr; position:static; padding:2rem}}

/* weight tables */
.rates-tables{margin-top:4.5rem}
.rates-tables h3{font-family:var(--font-display); font-weight:600; font-size:1.4rem; letter-spacing:-.02em; margin-bottom:.4rem}
.rt-grid{display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:1.2rem; margin-top:1.8rem}
.rt-card{background:var(--cream-3); border-radius:var(--r-md); padding:1.6rem; box-shadow:0 20px 40px -30px rgba(6,15,38,.35), inset 0 0 0 1px var(--line)}
.rt-card.hot{background:#fff; box-shadow:0 20px 40px -26px rgba(208,32,46,.4), inset 0 0 0 1.5px rgba(208,32,46,.35)}
.rt-head{display:flex; justify-content:space-between; font-size:.72rem; font-weight:600; letter-spacing:.14em; color:var(--ink-soft); padding-bottom:.9rem; border-bottom:1px dashed var(--line)}
.rt-card table{width:100%; border-collapse:collapse; margin-top:.5rem}
.rt-card td{padding:.5rem 0; font-size:.9rem; border-bottom:1px solid rgba(25,25,25,.06)}
.rt-card tr:last-child td{border-bottom:none}
.rt-card td:last-child{text-align:right; font-weight:600}

/* ---- built-in rate calculator ---- */
.rt-grid-calc{grid-template-columns:1fr; max-width:560px}
.rate-calc{
  position:relative; overflow:hidden;
  background:linear-gradient(158deg,#12275E 0%,#0B1836 55%,#060F26 100%);
  border-radius:var(--r-md); padding:2rem 2.1rem; color:#fff;
  box-shadow:0 34px 64px -34px rgba(6,15,38,.75), inset 0 0 0 1px rgba(255,255,255,.07);
  display:flex; flex-direction:column;
}
.rc-glow{position:absolute; top:-45%; right:-25%; width:65%; height:130%;
  background:radial-gradient(circle, rgba(208,32,46,.5), transparent 68%);
  filter:blur(24px); pointer-events:none}
.rc-top{position:relative; display:flex; justify-content:space-between; align-items:flex-start}
.rc-kicker{font-size:.7rem; letter-spacing:.2em; font-weight:600; color:var(--red-2)}
.rc-title{font-family:var(--font-display); font-weight:700; font-size:1.5rem; letter-spacing:-.02em; margin-top:.35rem}
.rc-badge{font-size:.68rem; font-weight:600; letter-spacing:.12em; padding:.4rem .75rem; border-radius:99px;
  background:rgba(255,255,255,.1); border:1px solid rgba(255,255,255,.16)}
.rc-weight{position:relative; display:flex; align-items:baseline; gap:.45rem; margin-top:1.7rem}
.rc-w-num{font-family:var(--font-display); font-weight:800; font-size:3.5rem; line-height:1; letter-spacing:-.03em; font-variant-numeric:tabular-nums}
.rc-w-unit{font-size:1.15rem; color:rgba(255,255,255,.55); font-weight:500}
.rc-slider{-webkit-appearance:none; appearance:none; width:100%; height:6px; border-radius:99px; margin-top:1.2rem; cursor:pointer;
  background:linear-gradient(90deg,var(--red-2),var(--red)) 0/var(--fill,14%) 100% no-repeat, rgba(255,255,255,.14)}
.rc-slider::-webkit-slider-thumb{-webkit-appearance:none; width:22px; height:22px; border-radius:50%; background:#fff;
  box-shadow:0 4px 14px rgba(0,0,0,.45), 0 0 0 4px rgba(208,32,46,.4); transition:transform .15s var(--ease)}
.rc-slider::-webkit-slider-thumb:hover{transform:scale(1.14)}
.rc-slider::-moz-range-thumb{width:22px; height:22px; border:none; border-radius:50%; background:#fff;
  box-shadow:0 4px 14px rgba(0,0,0,.45), 0 0 0 4px rgba(208,32,46,.4)}
.rc-scale{display:flex; justify-content:space-between; margin-top:.65rem; font-size:.72rem; color:rgba(255,255,255,.4)}
.rc-break{margin-top:1.7rem; border-top:1px solid rgba(255,255,255,.13); padding-top:1.1rem; display:flex; flex-direction:column; gap:.75rem}
.rc-row{display:flex; justify-content:space-between; font-size:.92rem; color:rgba(255,255,255,.72)}
.rc-row span:last-child{font-weight:600; color:#fff; font-variant-numeric:tabular-nums}
.rc-total{display:flex; justify-content:space-between; align-items:center; margin-top:1.3rem; padding:1.05rem 1.35rem; border-radius:18px;
  background:linear-gradient(150deg,var(--red-2),var(--red)); box-shadow:0 18px 34px -16px rgba(208,32,46,.65)}
.rc-total-l{font-size:.9rem; font-weight:500; color:rgba(255,255,255,.92)}
.rc-total-v{font-family:var(--font-display); font-weight:800; font-size:1.95rem; letter-spacing:-.02em; font-variant-numeric:tabular-nums}
.rc-foot{margin-top:1.15rem; font-size:.82rem; color:rgba(255,255,255,.5)}
.rc-foot a{color:#fff; text-decoration:underline; text-underline-offset:2px}
.rate-note{
  margin-top:2rem; padding:1.3rem 1.6rem; border-radius:var(--r-sm);
  background:var(--cream-3); box-shadow:inset 0 0 0 1px var(--line);
  display:flex; gap:1rem; align-items:flex-start; font-size:.95rem; line-height:1.6; color:var(--ink-soft)}
.rate-note svg{flex:none;margin-top:2px;stroke:var(--red);fill:none}
.rate-note b{color:var(--ink)}
.rate-note a{color:var(--red); font-weight:600; text-decoration:none}

/* ============ FAQ ============ */
#faq{background:var(--cream)}
.faq-head{text-align:center; max-width:640px; margin:0 auto 3.4rem}
.faq-head h2{margin-top:.6rem}
.faq-head .sub{margin-inline:auto}
.faq-cols{columns:2; column-gap:1.2rem; max-width:1000px; margin:0 auto}
.faq-item{break-inside:avoid; margin-bottom:1.2rem; background:var(--cream-3); border-radius:var(--r-md); box-shadow:inset 0 0 0 1px var(--line); overflow:hidden}
.faq-q{width:100%; text-align:left; background:none; border:none; cursor:pointer;
  display:flex; align-items:center; justify-content:space-between; gap:1rem;
  padding:1.4rem 1.6rem; font-family:var(--font-body); font-size:1.02rem; font-weight:500; color:var(--ink)}
.faq-q .ico{width:26px;height:26px;border-radius:50%;background:var(--navy-ink);flex:none;display:grid;place-items:center;transition:background .3s}
.faq-q .ico svg{width:12px;height:12px;stroke:#fff;stroke-width:2.4;transition:transform .3s var(--ease);fill:none}
.faq-item.open .faq-q .ico{background:var(--red)}
.faq-item.open .faq-q .ico svg{transform:rotate(45deg)}
.faq-a{max-height:0; overflow:hidden; transition:max-height .4s var(--ease)}
.faq-a p{padding:0 1.6rem 1.5rem; color:var(--ink-soft); font-size:.95rem; line-height:1.6}
@media(max-width:720px){.faq-cols{columns:1}}

/* ============ CONTACT ============ */
#contact-wrap{position:relative; background:var(--cream); padding-bottom:0}
.contact-echo{
  text-align:center; font-family:var(--font-display); font-weight:800;
  font-size:clamp(2.2rem,9vw,9rem); line-height:.85; letter-spacing:-.03em;
  text-transform:uppercase; white-space:nowrap;
  color:transparent;
  background:linear-gradient(180deg,#8d8b87 0%,#b4b2ad 42%,#e9e8e5 100%);
  -webkit-background-clip:text; background-clip:text;
  -webkit-text-fill-color:transparent;
  padding:3rem .5rem 0; margin-bottom:calc(-3.2vw - 1.2rem);
  position:relative; z-index:1; user-select:none; pointer-events:none;
}
.contact-echo .echo-br{display:none}
@media(max-width:820px){
  .contact-echo{
    white-space:normal;
    /* SHIPPING is 8 ultra-bold caps — 22vw overflowed the viewport. */
    font-size:clamp(2.2rem,11.5vw,4.2rem);
    line-height:.9;
    letter-spacing:-.04em;
    padding:2.4rem 6vw .35rem;
    margin-bottom:0;
    max-width:100%;
    box-sizing:border-box;
    overflow:visible;
    background:linear-gradient(180deg,#1c1b19 0%,#4a4844 62%,#7d7a74 100%);
    -webkit-background-clip:text; background-clip:text;
  }
  .contact-echo .echo-br{display:block}
}
.contact-card{
  position:relative; overflow:hidden; z-index:2;
  max-width:1240px; margin:0 auto; border-radius:var(--r-lg);
  background:var(--navy-ink); color:#fff;
  padding:5rem 6vw 0;
}
.contact-bg{position:absolute; inset:0; z-index:0; opacity:.16; filter:blur(1px);
  background-image:linear-gradient(rgba(255,255,255,.06) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.06) 1px,transparent 1px);
  background-size:60px 60px}
.contact-inner{position:relative; z-index:1; display:grid; grid-template-columns:1fr 1fr; gap:4rem; padding-bottom:4rem}
.contact-l h2{font-family:var(--font-display); font-weight:800; font-size:clamp(2.2rem,4.6vw,3.6rem); letter-spacing:-.03em; line-height:1.02}
.contact-l .cl-sub{margin-top:1rem; color:rgba(255,255,255,.66); font-size:1.05rem}
.contact-details{margin-top:2.6rem; display:flex; flex-direction:column; gap:1.3rem}
.cd-item .cd-l{font-size:.72rem; font-weight:600; letter-spacing:.16em; color:rgba(255,255,255,.5); text-transform:uppercase}
.cd-item .cd-v{margin-top:.35rem; font-size:1.08rem; font-weight:500}
.cd-item .cd-v a{color:#fff; text-decoration:none; border-bottom:1.5px solid var(--red)}
.form-field{margin-bottom:1.4rem}
.form-field label{display:block; font-size:.95rem; margin-bottom:.6rem; color:rgba(255,255,255,.85)}
.form-field input, .form-field textarea{
  width:100%; background:none; border:none; border-bottom:1px solid rgba(255,255,255,.24);
  color:#fff; font-family:var(--font-body); font-size:1rem; padding:.5rem 0; outline:none; transition:border-color .3s; resize:none}
.form-field input::placeholder, .form-field textarea::placeholder{color:rgba(255,255,255,.4)}
.form-field input:focus, .form-field textarea:focus{border-color:var(--red)}
.contact-form .pill{width:100%; justify-content:center; margin-top:.6rem}
/* email marquee */
.marquee{position:relative; z-index:1; border-top:1px solid rgba(255,255,255,.12); overflow:hidden; padding:1.6rem 0}
.marquee-track{display:flex; gap:2.4rem; width:max-content; animation:scrollx 26s linear infinite; align-items:center}
.marquee-track span{font-family:var(--font-display); font-weight:600; font-size:clamp(1.4rem,3vw,2.2rem); color:rgba(255,255,255,.28); white-space:nowrap; display:flex; align-items:center; gap:2.4rem}
.marquee-track .star{color:var(--red)}
@keyframes scrollx{to{transform:translateX(-50%)}}
@media(max-width:820px){.contact-inner{grid-template-columns:1fr; gap:2.4rem}}

/* ============ FOOTER ============ */
footer{position:relative; background:var(--cream); color:var(--ink); padding:5rem 6vw 2.5rem}
footer::before{
  content:'';
  position:absolute; top:0; left:0; right:0; height:3px;
  background:linear-gradient(90deg, var(--navy), var(--red));
}
.f-inner{max-width:1180px; margin:0 auto; display:flex; flex-wrap:wrap; gap:3rem; justify-content:space-between}
.f-tag{font-size:.92rem; color:var(--ink-soft); margin-top:1rem; max-width:30ch; line-height:1.6}
.f-col h4{font-size:.72rem; font-weight:600; letter-spacing:.16em; text-transform:uppercase; color:var(--ink-soft); margin-bottom:1.1rem}
.f-col a{display:block; text-decoration:none; color:var(--ink); opacity:.8; font-size:.95rem; padding:.3rem 0}
.f-col a:hover{opacity:1; color:var(--red)}
.f-bottom{max-width:1180px; margin:3.5rem auto 0; padding-top:1.6rem; border-top:1px solid var(--line);
  display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem; font-size:.82rem; color:var(--ink-soft)}
.f-credit a{
  color:inherit; text-decoration:none;
  border-bottom:1px solid transparent;
  padding:.35rem 0;
  transition:color .2s, border-color .2s;
}
.f-credit a:hover{color:var(--red); border-bottom-color:var(--red)}
.f-credit a:focus-visible{outline:2px solid var(--red); outline-offset:3px; border-radius:2px}
@media(max-width:640px){.f-bottom{flex-direction:column; align-items:flex-start; gap:.7rem}}

@media (prefers-reduced-motion: reduce){
  .reveal,.reveal-l,.reveal-r,.load,.head-split{opacity:1; transform:none}
  .marquee-track{animation:none}
  .hero h1 .chip,.hero h1 .chip.red,.hero h1 .chip svg,.hero-emoji{animation:none}
}
</style>
</head>
<body id="top">

<!-- availability badge — single continuous path, responsive width -->
<div class="availbar">
  <svg class="availbar-shape" viewBox="0 0 342 36" preserveAspectRatio="xMidYMid meet" aria-hidden="true">
    <!-- Agero notch profile: flat bottom (y=36), sides bow inward via cubics -->
    <path fill="currentColor" d="M 5.456 0 C -54.232 0 396.772 0 336.428 0 C 276.084 0 312.723 36 253.697 36 C 194.671 36 108.098 36 89.438 36 C 27.285 36 65.144 0 5.456 0 Z"/>
  </svg>
  <span class="availbar-row"><span class="dot"></span>Now serving Kingston &amp; Portmore</span>
</div>

<!-- ============ NAV ============ -->
<nav id="nav">
  <a class="brand" href="#top">
    <img class="brand-logo" src="{{ asset('images/tenants/today-shipping/logo.png') }}" alt="TODAY Shipping &amp; Logistics"
         onerror="this.style.display='none';this.nextElementSibling.style.display='inline-flex'">
    <span class="brand-fallback" style="display:none;align-items:center;gap:.6rem">
      <span class="mark"><svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.8" stroke-linejoin="round"><path d="M12 3 4 7v10l8 4 8-4V7l-8-4z"/><path d="M4 7l8 4 8-4M12 11v10"/></svg></span>
      <span class="wm">TO<i>D</i>AY</span>
    </span>
    <span class="brand-name">TODAY <i>SHIPPING</i></span>
  </a>
  <div class="nav-mid">
    <a href="#process">Process</a>
    <a href="#rates">Rates</a>
    <a href="#faq">FAQ</a>
    <a href="#contact">Contact</a>
  </div>
  <div class="nav-right">
    <a class="pill pill-outline nav-login" href="{{ route('login') }}">Log in</a>
    <a class="pill pill-dark nav-register" href="{{ route('register') }}">Create Account</a>
  </div>
</nav>

<!-- ============ HERO ============ -->
<div class="hero-wrap">
<header class="hero">
  <h1>Your trusted shipping partner<br>connecting Jamaica <span id="chipBox" class="hero-emoji">🇯🇲</span> to the <span id="chipDoor" class="hero-emoji">🌎</span> World</h1>
  <p class="lead load">Shop from your favourite US stores. We handle shipping, customs, and delivery — so your purchases arrive safely in Jamaica.</p>
  <div class="cta load">
    <a class="pill pill-red" href="{{ route('register') }}">Create Account
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
    <a class="pill pill-light" href="#rates">View Rates</a>
  </div>
  <div class="hero-mini load">
    <span><svg viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round"><path d="M20 6 9 17l-5-5"/></svg>Free US address</span>
    <span><svg viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round"><path d="M20 6 9 17l-5-5"/></svg>Customs handled for you</span>
    <span><svg viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round"><path d="M20 6 9 17l-5-5"/></svg>Air freight MIA → KIN</span>
  </div>
</header>
<!-- Moved outside <header class="hero"> deliberately: .hero has a CSS mask (the
     top notch cutout) which clips everything painted inside it, including this
     overlay. Living here in .hero-wrap instead means the flight path renders
     unclipped, on top of the hero. -->
<svg class="hero-flight" aria-hidden="true">
  <defs><mask id="flightReveal"><path class="flight-reveal"/></mask></defs>
  <path class="flight-trail" mask="url(#flightReveal)"/>
  <g class="flight-plane"><path d="M9 0 L-7 -5.5 L-2.5 0 L-7 5.5 Z"/></g>
</svg>
</div>

<!-- ============ PROCESS ============ -->
<section id="process">
  <div class="proc-wrap">
    <div class="sec-head reveal"><span class="eyebrow">(Process)</span></div>
    <h2 class="head-split" style="font-family:var(--font-display);font-weight:700;font-size:clamp(2rem,4.6vw,3.4rem);letter-spacing:-.025em;line-height:1.05">How your package gets home</h2>
    <p class="reveal" style="margin-top:1rem;color:var(--ink-soft);font-size:1.05rem;line-height:1.6;max-width:56ch">Four simple steps from checkout to your doorstep. You shop — we take care of the rest.</p>

    <!-- Row 1 -->
    <div class="proc-row">
      <div class="proc-name reveal">Shop US Retailers
        <div class="proc-tags">
          <span class="tag"><span class="d" style="background:#FF9900"></span>Amazon</span>
          <span class="tag"><span class="d" style="background:#E53238"></span>eBay</span>
          <span class="tag"><span class="d" style="background:#111"></span>SHEIN</span>
          <span class="tag"><span class="d" style="background:#F86800"></span>Temu</span>
        </div>
      </div>
      <div class="proc-vis photo reveal-l">
        <img src="https://plus.unsplash.com/premium_photo-1661299294569-24d4de22fcf0?w=900&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1yZWxhdGVkfDh8fHxlbnwwfHx8fHw%3D" alt="Online shopping on US retailer websites" loading="lazy">
        <span class="badge">STEP 01</span>
      </div>
      <div class="proc-detail reveal">
        <p>Browse and buy exactly as you always do. Your favourite stores, your prices, your choices — no middleman.</p>
        <div class="proc-meta">
          <div class="mrow"><span>Stores</span><span>Any US retailer</span></div>
          <div class="mrow"><span>Cost to you</span><span>Free</span></div>
        </div>
      </div>
    </div>

    <!-- Row 2 -->
    <div class="proc-row">
      <div class="proc-name reveal">Ship To Our US Address
        <div class="proc-tags">
          <span class="tag"><span class="d" style="background:var(--navy)"></span>Miami, FL Hub</span>
          <span class="tag"><span class="d" style="background:var(--red)"></span>Personal address</span>
        </div>
      </div>
      <div class="proc-vis photo reveal-r">
        <img src="https://plus.unsplash.com/premium_photo-1682129654490-433a048f73cf?q=80&w=2071&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="Warehouse staff receiving and preparing packages at our Miami, FL hub" loading="lazy">
        <span class="badge">STEP 02</span>
      </div>
      <div class="proc-detail reveal">
        <p>Use your personal US address at checkout. We receive, scan and prepare every package for its trip to Jamaica.</p>
        <div class="proc-meta">
          <div class="mrow"><span>Warehouse</span><span>Miami, FL</span></div>
          <div class="mrow"><span>Notified</span><span>On arrival</span></div>
        </div>
      </div>
    </div>

    <!-- Row 3 -->
    <div class="proc-row">
      <div class="proc-name reveal">We Clear Customs
        <div class="proc-tags">
          <span class="tag"><span class="d" style="background:var(--red)"></span>No forms</span>
          <span class="tag"><span class="d" style="background:#1F9D50"></span>Transparent duties</span>
        </div>
      </div>
      <div class="proc-vis photo reveal-l">
        <img src="https://images.unsplash.com/photo-1778791097093-1ec3a3536d00?q=80&w=1674&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="Customs clearance and documentation for incoming freight" loading="lazy">
        <span class="badge">STEP 03</span>
      </div>
      <div class="proc-detail reveal">
        <p>No queues, no paperwork, no surprises. Duties are calculated up front and your package clears while you carry on with your day.</p>
        <div class="proc-meta">
          <div class="mrow"><span>Paperwork</span><span>Handled for you</span></div>
          <div class="mrow"><span>Route</span><span>MIA → KIN, air</span></div>
        </div>
      </div>
    </div>

    <!-- Row 4 -->
    <div class="proc-row">
      <div class="proc-name reveal">Delivered Home
        <div class="proc-tags">
          <span class="tag"><span class="d" style="background:var(--navy)"></span>Kingston</span>
          <span class="tag"><span class="d" style="background:var(--navy)"></span>Portmore</span>
          <span class="tag"><span class="d" style="background:var(--red)"></span>Door delivery</span>
        </div>
      </div>
      <div class="proc-vis photo reveal-r">
        <img src="https://images.unsplash.com/photo-1633174524827-db00a6b7bc74?q=80&w=2096&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="Package delivered to a doorstep at home" loading="lazy">
        <span class="badge">STEP 04</span>
      </div>
      <div class="proc-detail reveal">
        <p>Your package is delivered straight to your door in Kingston and Portmore. No pickup needed — we bring it home to you.</p>
        <div class="proc-meta">
          <div class="mrow"><span>Service</span><span>Door delivery</span></div>
          <div class="mrow"><span>Areas</span><span>Kingston &amp; Portmore</span></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============ RATES ============ -->
<section id="rates">
  <div class="section">
    <div class="sec-head reveal"><span class="eyebrow">(Rates)</span></div>
    <h2 class="head-split">Simple, transparent <span class="r">pricing.</span></h2>
    <p class="sub reveal">Air freight from our Miami warehouse to Jamaica, billed by weight in JMD. No hidden charges — service fees shown upfront.</p>

    <div class="plan-stack">
      <!-- Personal -->
      <div class="plan light reveal">
        <div class="p-left">
          <div class="p-ic"><svg viewBox="0 0 24 24"><path d="M12 3 4 7v10l8 4 8-4V7l-8-4z"/><path d="M4 7l8 4 8-4"/></svg></div>
          <h3>Personal Shipper</h3>
          <p class="p-desc">Ideal for everyday online shoppers. Get a free US address and ship your purchases home by the pound.</p>
          <div class="p-meta"><span>Typical transit</span><span>3–5 business days</span></div>
        </div>
        <div class="p-right">
          <div class="price">$750<small> JMD / first lb</small></div>
          <ul class="p-feats">
            <li><span class="ck"><svg viewBox="0 0 24 24"><path d="M4 12.5 9.5 18 20 6.5"/></svg></span>Free personal US shipping address</li>
            <li><span class="ck"><svg viewBox="0 0 24 24"><path d="M4 12.5 9.5 18 20 6.5"/></svg></span>Billed by weight, priced in JMD</li>
            <li><span class="ck"><svg viewBox="0 0 24 24"><path d="M4 12.5 9.5 18 20 6.5"/></svg></span>Customs clearance included</li>
            <li><span class="ck"><svg viewBox="0 0 24 24"><path d="M4 12.5 9.5 18 20 6.5"/></svg></span>Weekday turnaround (Mon–Fri)</li>
          </ul>
          <a class="pill pill-dark" href="{{ route('register') }}">Get Started
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
        </div>
      </div>

      <!-- Business -->
      <div class="plan dark reveal">
        <div class="p-left">
          <div class="p-ic"><svg viewBox="0 0 24 24"><path d="M3 9l9-6 9 6v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M8 21v-8h8v8"/></svg></div>
          <h3>Bulk &amp; Business</h3>
          <p class="p-desc">For heavier loads and frequent shippers moving 20 lb and over. Dedicated handling and volume-friendly rates.</p>
          <div class="p-meta"><span>Handling fee</span><span>$600 JMD (20 lb+)</span></div>
        </div>
        <div class="p-right">
          <div class="price"><span class="accent">Custom</span><small> volume rates</small></div>
          <ul class="p-feats">
            <li><span class="ck"><svg viewBox="0 0 24 24"><path d="M4 12.5 9.5 18 20 6.5"/></svg></span>Priority handling on large parcels</li>
            <li><span class="ck"><svg viewBox="0 0 24 24"><path d="M4 12.5 9.5 18 20 6.5"/></svg></span>Rates for shipments over 30 lb</li>
            <li><span class="ck"><svg viewBox="0 0 24 24"><path d="M4 12.5 9.5 18 20 6.5"/></svg></span>Consolidation of multiple packages</li>
            <li><span class="ck"><svg viewBox="0 0 24 24"><path d="M4 12.5 9.5 18 20 6.5"/></svg></span>Direct support line</li>
          </ul>
          <a class="pill pill-red" href="{{ route('login') }}">Log in for rates
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
        </div>
      </div>
    </div>

    <!-- rate calculator -->
    <div class="rates-tables reveal">
      <div class="rt-grid rt-grid-calc">
        <div class="rate-calc" aria-label="Shipping rate calculator">
          <div class="rc-glow"></div>
          <div class="rc-top">
            <div>
              <div class="rc-kicker">INSTANT QUOTE</div>
              <div class="rc-title">Rate calculator</div>
            </div>
            <div class="rc-badge">JMD</div>
          </div>

          <div class="rc-weight">
            <span class="rc-w-num" id="rcWeight">5</span><span class="rc-w-unit">lb</span>
          </div>

          <input type="range" min="1" max="30" step="1" value="5" id="rcSlider" class="rc-slider"
                 aria-label="Package weight in pounds">
          <div class="rc-scale"><span>1 lb</span><span>15 lb</span><span>30 lb</span></div>

          <div class="rc-break">
            <div class="rc-row"><span>Base shipping rate</span><span id="rcBase">$2,000</span></div>
            <div class="rc-row"><span id="rcFeeLabel">Service fee</span><span id="rcFee">$300</span></div>
          </div>

          <div class="rc-total">
            <span class="rc-total-l">Estimated total</span>
            <span class="rc-total-v" id="rcTotal">$2,300</span>
          </div>

          <div class="rc-foot">Shipping over 30 lb? <a href="{{ route('login') }}">Log in for rates</a></div>
        </div>
      </div>
      <div class="rate-note">
        <svg width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="9.5"/><path d="M12 8v5M12 16.5v.01"/></svg>
        <span>Packages from <b>1–19 lb</b> incur a <b>$300 JMD</b> service fee. Packages <b>20 lb and over</b> incur a <b>$600 JMD</b> handling fee. Shipping over 30 lb? <a href="{{ route('login') }}">Log in to view rates</a>.</span>
      </div>
    </div>
  </div>
</section>

<!-- ============ FAQ ============ -->
<section id="faq">
  <div class="section">
    <div class="faq-head reveal">
      <span class="eyebrow">(FAQs)</span>
      <h2 class="head-split">Your questions, answered</h2>
      <p class="sub">Everything you need to know about shopping and shipping with TODAY.</p>
    </div>
    <div class="faq-cols">
      <div class="faq-item reveal">
        <button class="faq-q">How do I get a US shipping address?<span class="ico"><svg viewBox="0 0 24 24" fill="none" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg></span></button>
        <div class="faq-a"><p>Create a free account and we'll assign you a personal US address in Miami. Use it at checkout on any US store and your packages route straight to our warehouse.</p></div>
      </div>
      <div class="faq-item reveal">
        <button class="faq-q">How long does shipping take?<span class="ico"><svg viewBox="0 0 24 24" fill="none" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg></span></button>
        <div class="faq-a"><p>Most packages arrive in Jamaica within 3–5 business days of reaching our Miami hub, moving by air freight from Miami to Kingston.</p></div>
      </div>
      <div class="faq-item reveal">
        <button class="faq-q">How is shipping priced?<span class="ico"><svg viewBox="0 0 24 24" fill="none" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg></span></button>
        <div class="faq-a"><p>By weight, in Jamaican dollars. Rates start at $750 JMD for the first pound. A $300 service fee applies to packages 1–19 lb, and a $600 handling fee applies at 20 lb and over.</p></div>
      </div>
      <div class="faq-item reveal">
        <button class="faq-q">Do you handle customs clearance?<span class="ico"><svg viewBox="0 0 24 24" fill="none" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg></span></button>
        <div class="faq-a"><p>Yes. We handle customs paperwork and clearance for you. Duties are calculated transparently and shown before your package is released — no forms, no queues.</p></div>
      </div>
      <div class="faq-item reveal">
        <button class="faq-q">How do I receive my packages?<span class="ico"><svg viewBox="0 0 24 24" fill="none" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg></span></button>
        <div class="faq-a"><p>We currently offer delivery only. Once your package clears customs, it's delivered directly to your door — no pickup needed. You'll get a notification when it's on the way.</p></div>
      </div>
      <div class="faq-item reveal">
        <button class="faq-q">How do I track my shipment?<span class="ico"><svg viewBox="0 0 24 24" fill="none" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg></span></button>
        <div class="faq-a"><p>Log in to your dashboard any time to see live status — received, packed, in transit, cleared, and out for delivery.</p></div>
      </div>
    </div>
  </div>
</section>

<!-- ============ CONTACT ============ -->
<div id="contact" style="position:relative;top:-4rem"></div>
<div id="contact-wrap">
  <div class="contact-echo">TODAY <span class="echo-br"></span>SHIPPING</div>
  <div class="contact-card reveal">
    <div class="contact-bg"></div>
    <div class="contact-inner">
      <div class="contact-l">
        <h2 class="head-split">Got a package coming?</h2>
        <p class="cl-sub">Let's get it home. Reach out and our team will help you get started.</p>
        <div class="contact-details">
          <div class="cd-item"><div class="cd-l">Visit us</div><div class="cd-v">Kingston &amp; Portmore, Jamaica</div></div>
          <div class="cd-item"><div class="cd-l">Call us</div><div class="cd-v"><a href="tel:+18763697319">876-369-7319</a></div></div>
          <div class="cd-item"><div class="cd-l">Email us</div><div class="cd-v"><a href="mailto:support@todayshippingja.com">support@todayshippingja.com</a></div></div>
        </div>
      </div>
      <div class="contact-r">
        <form class="contact-form" id="contactForm">
          <div class="form-field"><label>Your Name</label><input type="text" name="name" placeholder="Enter your name" required></div>
          <div class="form-field"><label>Your Email</label><input type="email" name="email" placeholder="Enter your email" required></div>
          <div class="form-field"><label>Message</label><textarea name="msg" rows="3" placeholder="How can we help?"></textarea></div>
          <button type="submit" class="pill pill-red">Send Now!
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></button>
        </form>
      </div>
    </div>
    <div class="marquee">
      <div class="marquee-track">
        <span>support@todayshippingja.com <b class="star">✳</b></span>
        <span>support@todayshippingja.com <b class="star">✳</b></span>
        <span>support@todayshippingja.com <b class="star">✳</b></span>
        <span>support@todayshippingja.com <b class="star">✳</b></span>
        <span>support@todayshippingja.com <b class="star">✳</b></span>
        <span>support@todayshippingja.com <b class="star">✳</b></span>
      </div>
    </div>
  </div>
</div>

<!-- ============ FOOTER ============ -->
<footer>
  <div class="f-inner">
    <div>
      <a class="brand" href="#top">
        <img class="brand-logo" src="{{ asset('images/tenants/today-shipping/logo.png') }}" alt="TODAY Shipping &amp; Logistics"
             onerror="this.style.display='none';this.nextElementSibling.style.display='inline-flex'">
        <span class="brand-fallback" style="display:none;align-items:center;gap:.6rem">
          <span class="mark"><svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.8" stroke-linejoin="round"><path d="M12 3 4 7v10l8 4 8-4V7l-8-4z"/><path d="M4 7l8 4 8-4M12 11v10"/></svg></span>
          <span class="wm">TO<i>D</i>AY</span>
        </span>
        <span class="brand-name">TODAY <i>SHIPPING</i></span>
      </a>
      <p class="f-tag">Your trusted shipping partner. Shop TODAY, Ship TODAY.</p>
    </div>
    <div class="f-col">
      <h4>Company</h4>
      <a href="#process">Process</a>
      <a href="#rates">Rates</a>
      <a href="#faq">FAQ</a>
      <a href="#contact">Contact</a>
    </div>
    <div class="f-col">
      <h4>Get Started</h4>
      <a href="{{ route('login') }}">Log in</a>
      <a href="{{ route('register') }}">Create Account</a>
    </div>
    <div class="f-col">
      <h4>Popular Stores</h4>
      <a href="https://www.amazon.com" target="_blank" rel="noopener noreferrer">Amazon</a>
      <a href="https://www.ebay.com" target="_blank" rel="noopener noreferrer">eBay</a>
      <a href="https://www.shein.com" target="_blank" rel="noopener noreferrer">SHEIN</a>
      <a href="https://www.fashionnova.com" target="_blank" rel="noopener noreferrer">Fashion Nova</a>
    </div>
    <div class="f-col">
      <h4>Reach Us</h4>
      <a href="tel:+18763697319">876-369-7319</a>
      <a href="mailto:support@todayshippingja.com">support@todayshippingja.com</a>
      <a href="https://www.instagram.com/TODAYShipppingandLogistics" target="_blank" rel="noopener noreferrer">Instagram</a>
    </div>
  </div>
  <div class="f-bottom">
    <span>© 2026 TODAY Shipping &amp; Logistics. All rights reserved.</span>
    <span class="f-credit">Developed by <a href="https://quantaradigital.co.uk/" target="_blank" rel="noopener noreferrer">Quantara Digital</a></span>
    <span>KGN · POR · MIA</span>
  </div>
</footer>

<!-- ============ SCRIPTS ============ -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.13.0/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.13.0/ScrollTrigger.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.13.0/SplitText.min.js"></script>
<script>
(function(){
  /* ---- sticky nav ---- */
  const nav = document.getElementById('nav');
  const onScroll = () => nav.classList.toggle('solid', window.scrollY > 40);
  onScroll(); addEventListener('scroll', onScroll, {passive:true});

  /* ---- FAQ accordion ---- */
  document.querySelectorAll('.faq-item').forEach(item=>{
    const q = item.querySelector('.faq-q');
    const a = item.querySelector('.faq-a');
    q.addEventListener('click', ()=>{
      const open = item.classList.contains('open');
      document.querySelectorAll('.faq-item.open').forEach(o=>{
        o.classList.remove('open'); o.querySelector('.faq-a').style.maxHeight = null;
      });
      if(!open){ item.classList.add('open'); a.style.maxHeight = a.scrollHeight + 'px'; }
    });
  });

  /* ---- contact form → mailto ---- */
  const form = document.getElementById('contactForm');
  if(form){
    form.addEventListener('submit', e=>{
      e.preventDefault();
      const d = new FormData(form);
      const body = encodeURIComponent('Name: '+(d.get('name')||'')+'\n\n'+(d.get('msg')||''));
      const subj = encodeURIComponent('Website enquiry from '+(d.get('name')||'a customer'));
      window.location.href = 'mailto:support@todayshippingja.com?subject='+subj+'&body='+body;
    });
  }

  /* ---- animations ---- */
  const reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;
  if(window.gsap && window.ScrollTrigger && !reduced){
    gsap.registerPlugin(ScrollTrigger);
    if(window.SplitText) gsap.registerPlugin(SplitText);

    /* nav bar fades in as the hero title begins revealing */
    gsap.from('#nav', {opacity:0, y:-14, duration:.9, ease:'power3.out', delay:.2});

    /* hero title — per-letter blur-focus cascade (FlipWords enter effect).
       Each character fades up from y:10 while un-blurring from blur(8px)→0,
       staggered left-to-right so letters snap into focus in sequence.
       autoSplit re-runs the split (and animation) once the web font loads. */
    const titleEl = document.querySelector('.hero h1');
    if(window.SplitText && titleEl){
      SplitText.create(titleEl, {
        type:'lines,words,chars', autoSplit:true,
        onSplit(self){
          gsap.set(titleEl,{opacity:1});
          return gsap.from(self.chars, {
            opacity:0, y:10, filter:'blur(8px)',
            duration:.5, ease:'power2.out',
            stagger:.035, delay:.2
          });
        }
      });
    } else if(titleEl){
      gsap.set(titleEl,{opacity:1});
      gsap.from(titleEl, {opacity:0, y:10, filter:'blur(8px)', duration:.8, ease:'power2.out', delay:.2});
    }

    /* section headers — same blur-focus cascade, triggered as each scrolls in */
    document.querySelectorAll('.head-split').forEach(h=>{
      if(window.SplitText){
        SplitText.create(h, {
          type:'lines,words,chars', autoSplit:true,
          onSplit(self){
            gsap.set(h,{opacity:1});
            return gsap.from(self.chars, {
              opacity:0, y:10, filter:'blur(8px)',
              duration:.5, ease:'power2.out', stagger:.025,
              scrollTrigger:{trigger:h, start:'top 85%', once:true}
            });
          }
        });
      } else {
        gsap.set(h,{opacity:1});
        gsap.from(h, {opacity:0, y:10, filter:'blur(8px)', duration:.8, ease:'power2.out',
          scrollTrigger:{trigger:h, start:'top 85%', once:true}});
      }
    });

    /* rest of hero fades up just after the title starts revealing */
    gsap.set('.load',{opacity:0, y:34});
    gsap.to('.load',{opacity:1, y:0, duration:1, ease:'power3.out', stagger:.13, delay:.55});

    /* fade-up on scroll */
    gsap.utils.toArray('.reveal').forEach(el=>{
      gsap.to(el,{opacity:1, y:0, duration:.9, ease:'power3.out',
        scrollTrigger:{trigger:el, start:'top 88%'}});
    });
    /* slide-in from the left */
    gsap.utils.toArray('.reveal-l').forEach(el=>{
      gsap.to(el,{opacity:1, x:0, duration:1.05, ease:'power3.out',
        scrollTrigger:{trigger:el, start:'top 85%'}});
    });
    /* slide-in from the right */
    gsap.utils.toArray('.reveal-r').forEach(el=>{
      gsap.to(el,{opacity:1, x:0, duration:1.05, ease:'power3.out',
        scrollTrigger:{trigger:el, start:'top 85%'}});
    });

    addEventListener('resize', ()=>{
      document.querySelectorAll('.faq-item.open .faq-a').forEach(a=>a.style.maxHeight=a.scrollHeight+'px');
      ScrollTrigger.refresh();
    });
  } else {
    document.querySelectorAll('.hero h1,.load,.reveal,.reveal-l,.reveal-r,.head-split')
      .forEach(el=>{el.style.opacity=1; el.style.transform='none';});
  }
})();

/* ---- rate calculator ---- */
(function(){
  const rates={1:750,2:900,3:1400,4:1700,5:2000,6:2200,7:2500,8:2800,9:3000,10:3500,
    11:3800,12:4100,13:4500,14:4800,15:5100,16:5400,17:5700,18:6000,19:6300,20:6600,
    21:6900,22:7200,23:7500,24:7800,25:8100,26:8400,27:8700,28:9000,29:9300,30:9600};
  const slider=document.getElementById('rcSlider');
  if(!slider) return;
  const fmt=n=>'$'+n.toLocaleString('en-US');
  const wEl=document.getElementById('rcWeight'),
        baseEl=document.getElementById('rcBase'),
        feeEl=document.getElementById('rcFee'),
        feeLabel=document.getElementById('rcFeeLabel'),
        totalEl=document.getElementById('rcTotal');
  function update(){
    const w=+slider.value, base=rates[w], fee=w>=20?600:300;
    slider.style.setProperty('--fill',((w-1)/29*100)+'%');
    wEl.textContent=w;
    baseEl.textContent=fmt(base);
    feeLabel.textContent=w>=20?'Handling fee':'Service fee';
    feeEl.textContent=fmt(fee);
    totalEl.textContent=fmt(base+fee);
    totalEl.animate([{transform:'scale(1.06)'},{transform:'scale(1)'}],{duration:180,easing:'ease-out'});
  }
  slider.addEventListener('input',update);
  update();
})();

/* ---- hero flight: plane takes off from the box chip, draws a trail, lands at the door chip ---- */
(function(){
  const hero=document.querySelector('.hero'),
        svg=document.querySelector('.hero-flight');
  if(!hero||!svg||!window.gsap) return;
  if(matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  const trail=svg.querySelector('.flight-trail'),
        reveal=svg.querySelector('.flight-reveal'),
        plane=svg.querySelector('.flight-plane');
  let tl,total;
  const center=el=>{const r=el.getBoundingClientRect(),h=hero.getBoundingClientRect();
    return {x:r.left-h.left+r.width/2, y:r.top-h.top+r.height/2};};
  function at(prog){
    const len=total*prog,
          p=trail.getPointAtLength(len),
          p2=trail.getPointAtLength(Math.min(total,len+1.5)),
          ang=Math.atan2(p2.y-p.y,p2.x-p.x)*180/Math.PI;
    plane.setAttribute('transform',`translate(${p.x} ${p.y}) rotate(${ang}) scale(1.8)`);
    // The reveal path is an invisible white stroke inside <mask>, driven by
    // the classic dash-length/dashoffset "draw-on" trick — it shows only the
    // portion of the (rectangular-dashed) trail the plane has already flown
    // over, rather than the whole route being visible at once.
    reveal.style.strokeDashoffset=total*(1-prog);
  }
  function build(){
    // Re-query on every build: SplitText's autoSplit rebuilds the h1's
    // markup when the web font loads, which can replace these chip nodes.
    // Caching them once at page load risks holding stale references that
    // silently produce a zero-size rect (and an invisible/garbled path).
    const box=document.getElementById('chipBox'),
          door=document.getElementById('chipDoor');
    if(!box||!door) return;
    const boxRect=box.getBoundingClientRect(), doorRect=door.getBoundingClientRect();
    if(!boxRect.width || !doorRect.width) return;

    if(tl) tl.kill();
    const h=hero.getBoundingClientRect();
    svg.setAttribute('viewBox',`0 0 ${h.width} ${h.height}`);
    const a=center(box), b=center(door),
          cx=(a.x+b.x)/2,
          cy=Math.min(a.y,b.y)-Math.max(90,Math.abs(b.x-a.x)*0.55);
    const d=`M ${a.x} ${a.y} Q ${cx} ${cy} ${b.x} ${b.y}`;
    trail.setAttribute('d',d);
    reveal.setAttribute('d',d);
    total=trail.getTotalLength();
    reveal.style.strokeDasharray=total+' '+total;
    at(0);
    const st={p:0};
    tl=gsap.timeline({repeat:-1, repeatDelay:1.8});
    tl.set(trail,{opacity:.8})
      .set(plane,{opacity:0})
      .call(()=>at(0))
      .to(plane,{opacity:1,duration:.25,ease:'power2.out'})
      .to(st,{p:1,duration:2.6,ease:'power1.inOut',onUpdate:()=>at(st.p)},'<')
      .to(plane,{opacity:0,duration:.3,ease:'power2.in'})
      .to(trail,{opacity:0,duration:.7,ease:'power2.in'},'<');
  }
  let rt; addEventListener('resize',()=>{clearTimeout(rt); rt=setTimeout(build,220);});
  // Wait for the font (and SplitText's font-triggered re-split) to settle
  // before the first build, rather than building immediately and then
  // killing/rebuilding a few hundred ms later mid-flight — that restart
  // is what made the plane look like it vanished right after takeoff.
  if(document.fonts&&document.fonts.ready){
    document.fonts.ready.then(()=>setTimeout(build,150));
  } else {
    build();
  }
})();
</script>
</body>
</html>
