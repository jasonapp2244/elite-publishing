/* tour.js — Elite Publishing site preview/tour, shared by the app bundle
   AND the standalone /author-website/ campaign page. Single source of truth.
   Loaded BEFORE the app bundle (defer preserves order); all classic scripts
   share one global scope, so the bundle's showWpTab() etc. see these globals. */
/* ============================================================
   "See your site" — in-app guided tour of an Elite Publishing site.
   A look-only preview shown BEFORE an author buys hosting. Public-site
   half: navigate the pages and switch genre styles live. Colors/fonts
   mirror the real style packs so it's an honest preview. One real book
   (The Vinyl Dialogues, by Mike Morsch / Biblio Publishing) with its
   real Amazon links makes the buy buttons genuinely live.
   ============================================================ */
const TOUR_PACKS = {
  premium:   { label:'Premium',      primary:'#1A1A1A', second:'#D4AF37', bg:'#FFFFFF', text:'#2E2E2E', heading:'Libre Baskerville', body:'Source Serif 4', gfonts:'Libre+Baskerville:wght@400;700&family=Source+Serif+4:wght@400;500;600' },
  literary:  { label:'Literary',     primary:'#1D3557', second:'#C8A96B', bg:'#F8F3E9', text:'#2E2A24', heading:'Playfair Display', body:'Lora', gfonts:'Playfair+Display:wght@500;700;900&family=Lora:wght@400;500;600' },
  cozy:      { label:'Cozy Mystery', primary:'#4F7186', second:'#A7B8A0', bg:'#FAF7F2', text:'#333333', heading:'Cormorant Garamond', body:'Source Sans 3', gfonts:'Cormorant+Garamond:wght@500;600;700&family=Source+Sans+3:wght@400;500;600' },
  thriller:  { label:'Thriller',     primary:'#111111', second:'#8B0000', bg:'#FFFFFF', text:'#1A1A1A', heading:'Oswald', body:'Open Sans', gfonts:'Oswald:wght@400;500;600;700&family=Open+Sans:wght@400;500;600' },
  romance:   { label:'Romance',      primary:'#C98B98', second:'#D4B483', bg:'#FFF8F2', text:'#54454A', heading:'Cinzel', body:'Montserrat', gfonts:'Cinzel:wght@500;600;700&family=Montserrat:wght@400;500;600' },
  fantasy:   { label:'Fantasy',      primary:'#234E52', second:'#C9A227', bg:'#F4E8D0', text:'#3A2D28', heading:'Cinzel Decorative', body:'Merriweather', gfonts:'Cinzel+Decorative:wght@400;700;900&family=Merriweather:wght@400;700' },
  scifi:     { label:'Sci-Fi',       primary:'#0B132B', second:'#3A86FF', bg:'#EAEDF2', text:'#1A1F30', heading:'Orbitron', body:'Roboto', gfonts:'Orbitron:wght@500;700;900&family=Roboto:wght@400;500;700' },
  nonfiction:{ label:'Nonfiction',   primary:'#1B365D', second:'#2F80ED', bg:'#F5F7FA', text:'#333333', heading:'Poppins', body:'Inter', gfonts:'Poppins:wght@500;600;700&family=Inter:wght@400;500;600' },
  childrens: { label:"Children's",   primary:'#4CC9F0', second:'#FF6B6B', bg:'#FFFFFF', text:'#2E3A40', heading:'Fredoka', body:'Nunito', gfonts:'Fredoka:wght@500;600;700&family=Nunito:wght@400;600;700' },
  indie:     { label:'Modern Indie', primary:'#222831', second:'#00ADB5', bg:'#EEEEEE', text:'#222831', heading:'Montserrat', body:'Work Sans', gfonts:'Montserrat:wght@500;600;700&family=Work+Sans:wght@400;500;600' }
};

const TOUR_BOOK = {
  series:  'The Vinyl Dialogues',
  tagline: 'The stories behind the records — from the artists who made them.',
  author:  'Mike Morsch',
  cover:   'assets/demo/vinyl-dialogues.jpg',
  price:   '$24.95',
  blurb:   'Music journalist Mike Morsch sits down with the artists behind some of the most memorable albums of the 1970s — the sessions, the stories, and the songs that made them.',
  amazonPrint:  'https://www.amazon.com/Vinyl-Dialogues-Stories-Memorable-Artists/dp/1622492072',
  amazonKindle: 'https://www.amazon.com/Vinyl-Dialogues-Stories-memorable-artists-ebook/dp/B00KI1QW7Q'
};

let tourState = { page: 'home', pack: 'premium', target: 'wp-tour', mode: 'public', manageTab: 'style', editBook: false };

/* Open the tour as a public, login-free modal from the landing page. */
function openSiteTour() {
  tourState.target = 'site-tour-body';
  tourState.page = 'home';
  tourState.mode = 'public';
  const ov = document.getElementById('site-tour-overlay');
  if (ov) { ov.style.display = 'flex'; document.body.style.overflow = 'hidden'; }
  renderTour();
}
function closeSiteTour() {
  const ov = document.getElementById('site-tour-overlay');
  if (ov) { ov.style.display = 'none'; document.body.style.overflow = ''; }
}

function tourTextOn(hex) {
  const c = hex.replace('#', '');
  const r = parseInt(c.substr(0, 2), 16), g = parseInt(c.substr(2, 2), 16), b = parseInt(c.substr(4, 2), 16);
  return (0.299 * r + 0.587 * g + 0.114 * b) > 150 ? '#1a1a1a' : '#ffffff';
}

function tourLoadFont(p) {
  let link = document.getElementById('ah-tour-font');
  if (!link) {
    link = document.createElement('link');
    link.id = 'ah-tour-font';
    link.rel = 'stylesheet';
    document.head.appendChild(link);
  }
  link.href = 'https://fonts.googleapis.com/css2?family=' + p.gfonts + '&display=swap';
}

function tourGo(page) { tourState.page = page; renderTour(); }
function tourStyle(slug) { tourState.pack = slug; tourState.page = 'home'; renderTour(); }
function tourMode(mode) { tourState.mode = mode; if (mode === 'public') tourState.page = 'home'; else { tourState.manageTab = 'style'; tourState.editBook = false; } renderTour(); }
function tourManageTab(tab) { tourState.manageTab = tab; tourState.editBook = false; renderTour(); }
function tourEditBook(on) { tourState.editBook = !!on; renderTour(); }

function tourNav(p) {
  const onBg = tourTextOn(p.bg);
  const item = (page, label) => {
    const active = tourState.page === page;
    return '<span onclick="tourGo(\'' + page + '\')" style="cursor:pointer;font-size:13px;color:' + onBg + ';opacity:' + (active ? '1' : '.6') + ';border-bottom:2px solid ' + (active ? p.second : 'transparent') + ';padding-bottom:3px">' + label + '</span>';
  };
  return '<div style="display:flex;align-items:center;justify-content:space-between;padding:14px 20px;background:' + p.bg + ';border-bottom:1px solid rgba(0,0,0,.08)">' +
    '<span style="font-family:\'' + p.heading + '\',Georgia,serif;font-size:17px;color:' + p.text + '">' + TOUR_BOOK.author + '</span>' +
    '<div style="display:flex;gap:18px">' + item('home', 'Home') + item('books', 'Books') + item('blog', 'Blog') + item('contact', 'Contact') + '</div>' +
    '</div>';
}

function tourCover(w) {
  return '<img src="' + TOUR_BOOK.cover + '" alt="' + TOUR_BOOK.series + '" onerror="this.style.visibility=\'hidden\'" style="display:block;width:' + w + 'px;height:auto;border-radius:4px;box-shadow:0 6px 20px rgba(0,0,0,.25)">';
}

function tourPageHome(p) {
  const onSecond = tourTextOn(p.second);
  return '<div style="background:' + p.text + ';color:' + p.bg + ';padding:34px 28px;display:flex;align-items:center;gap:26px">' +
      '<div style="flex:1;min-width:0">' +
        '<div style="font-family:\'' + p.heading + '\',Georgia,serif;font-size:27px;line-height:1.2">' + TOUR_BOOK.tagline + '</div>' +
        '<div style="font-family:\'' + p.body + '\',Georgia,serif;font-size:14px;opacity:.85;line-height:1.6;margin:12px 0 18px">' + TOUR_BOOK.blurb + '</div>' +
        '<span onclick="tourGo(\'book\')" style="cursor:pointer;display:inline-block;background:' + p.second + ';color:' + onSecond + ';font-family:\'' + p.body + '\',sans-serif;font-size:14px;padding:10px 22px;border-radius:6px">Browse the books</span>' +
      '</div>' +
      '<div onclick="tourGo(\'book\')" style="cursor:pointer;flex:0 0 auto">' + tourCover(120) + '</div>' +
    '</div>' +
    '<div style="background:' + p.bg + ';color:' + p.text + ';padding:22px 28px">' +
      '<div style="font-family:\'' + p.heading + '\',Georgia,serif;font-size:18px;margin-bottom:14px">The books</div>' +
      '<div onclick="tourGo(\'book\')" style="cursor:pointer;display:flex;gap:16px;align-items:center">' + tourCover(70) +
        '<div><div style="font-family:\'' + p.heading + '\',Georgia,serif;font-size:16px">' + TOUR_BOOK.series + '</div>' +
        '<div style="font-family:\'' + p.body + '\',sans-serif;font-size:13px;opacity:.7;margin-top:3px">Print &amp; Kindle · ' + TOUR_BOOK.price + '</div></div>' +
      '</div>' +
      '<div style="border-top:1px solid rgba(0,0,0,.08);margin-top:22px;padding-top:18px;display:flex;gap:16px;align-items:flex-start">' +
        '<div style="flex:0 0 56px;height:56px;border-radius:50%;background:' + p.second + ';opacity:.5"></div>' +
        '<div style="font-family:\'' + p.body + '\',sans-serif"><div style="font-family:\'' + p.heading + '\',Georgia,serif;font-size:15px;margin-bottom:4px">About ' + TOUR_BOOK.author + '</div>' +
        '<div style="font-size:13px;opacity:.75;line-height:1.6">Mike is a longtime music journalist and the voice behind the Vinyl Dialogues series, where the artists tell the stories themselves.</div></div>' +
      '</div>' +
    '</div>';
}

function tourPageBooks(p) {
  return '<div style="background:' + p.bg + ';color:' + p.text + ';padding:26px 28px">' +
    '<div style="font-family:\'' + p.heading + '\',Georgia,serif;font-size:20px;margin-bottom:18px">Books</div>' +
    '<div onclick="tourGo(\'book\')" style="cursor:pointer;display:inline-block;width:160px">' + tourCover(160) +
      '<div style="font-family:\'' + p.heading + '\',Georgia,serif;font-size:15px;margin-top:10px">' + TOUR_BOOK.series + '</div>' +
      '<div style="font-family:\'' + p.body + '\',sans-serif;font-size:13px;opacity:.7;margin-top:2px">' + TOUR_BOOK.price + '</div>' +
    '</div></div>';
}

function tourPageBook(p) {
  const onSecond = tourTextOn(p.second);
  const btn = (href, label) => '<a href="' + href + '" target="_blank" rel="noopener" style="display:inline-block;background:' + p.second + ';color:' + onSecond + ';font-family:\'' + p.body + '\',sans-serif;font-size:14px;text-decoration:none;padding:11px 22px;border-radius:6px;margin:0 10px 10px 0">' + label + ' ↗</a>';
  return '<div style="background:' + p.bg + ';color:' + p.text + ';padding:28px;display:flex;gap:28px;align-items:flex-start;flex-wrap:wrap">' +
    '<div style="flex:0 0 auto">' + tourCover(190) + '</div>' +
    '<div style="flex:1;min-width:230px">' +
      '<div style="font-family:\'' + p.heading + '\',Georgia,serif;font-size:24px;line-height:1.2">' + TOUR_BOOK.series + '</div>' +
      '<div style="font-family:\'' + p.body + '\',sans-serif;font-size:14px;opacity:.7;margin:6px 0 14px">by ' + TOUR_BOOK.author + ' · ' + TOUR_BOOK.price + '</div>' +
      '<div style="font-family:\'' + p.body + '\',sans-serif;font-size:14px;line-height:1.7;margin-bottom:20px">' + TOUR_BOOK.blurb + '</div>' +
      btn(TOUR_BOOK.amazonPrint, 'Print on Amazon') + btn(TOUR_BOOK.amazonKindle, 'eBook on Kindle') +
      '<div style="margin-top:6px"><span style="display:inline-block;border:1px solid ' + p.text + ';opacity:.55;color:' + p.text + ';font-family:\'' + p.body + '\',sans-serif;font-size:14px;padding:10px 22px;border-radius:6px">Buy in Print — ' + TOUR_BOOK.price + '</span>' +
      '<div style="font-size:12px;opacity:.6;margin-top:7px;font-family:\'' + p.body + '\',sans-serif">Print sales check out right on your own site — your store handles it.</div></div>' +
    '</div></div>';
}

function tourPageBlog(p) {
  const posts = [
    ['Five albums that owned the summer of ’74', 'June 2026', 'A look back at the records that never left the turntable that year.'],
    ['What the artists remember that the liner notes left out', 'May 2026', 'The small studio moments that shaped some very big songs.'],
    ['Writing the Vinyl Dialogues: how the interviews come together', 'April 2026', 'A peek behind the curtain at the conversations themselves.']
  ];
  let html = '<div style="background:' + p.bg + ';color:' + p.text + ';padding:26px 28px"><div style="font-family:\'' + p.heading + '\',Georgia,serif;font-size:20px;margin-bottom:6px">Blog</div>';
  posts.forEach(function (po) {
    html += '<div style="border-top:1px solid rgba(0,0,0,.08);padding:16px 0">' +
      '<div style="font-family:\'' + p.heading + '\',Georgia,serif;font-size:16px">' + po[0] + '</div>' +
      '<div style="font-family:\'' + p.body + '\',sans-serif;font-size:12px;opacity:.6;margin:3px 0 6px">' + po[1] + '</div>' +
      '<div style="font-family:\'' + p.body + '\',sans-serif;font-size:13px;opacity:.8;line-height:1.6">' + po[2] + '</div></div>';
  });
  return html + '</div>';
}

function tourPageContact(p) {
  const fld = (label) => '<div style="margin-bottom:12px"><div style="font-family:\'' + p.body + '\',sans-serif;font-size:13px;margin-bottom:5px">' + label + '</div><div style="height:' + (label === 'Message' ? '70' : '34') + 'px;background:#fff;border:1px solid rgba(0,0,0,.18);border-radius:5px"></div></div>';
  return '<div style="background:' + p.bg + ';color:' + p.text + ';padding:26px 28px">' +
    '<div style="font-family:\'' + p.heading + '\',Georgia,serif;font-size:20px;margin-bottom:6px">Contact</div>' +
    '<div style="font-family:\'' + p.body + '\',sans-serif;font-size:13px;opacity:.75;line-height:1.6;margin-bottom:16px">Readers reach you through a simple form — messages land straight in your inbox.</div>' +
    '<div style="max-width:380px">' + fld('Your name') + fld('Email') + fld('Message') +
    '<span style="display:inline-block;background:' + p.second + ';color:' + tourTextOn(p.second) + ';font-family:\'' + p.body + '\',sans-serif;font-size:14px;padding:9px 22px;border-radius:6px">Send</span></div></div>';
}

function tourModeToggle() {
  const btn = (mode, label, sub) => {
    const on = tourState.mode === mode;
    return '<button onclick="tourMode(\'' + mode + '\')" style="flex:1;cursor:pointer;text-align:left;border:1px solid ' + (on ? 'var(--accent)' : 'var(--ink-faint)') + ';background:' + (on ? 'var(--accent)' : 'var(--white)') + ';color:' + (on ? 'var(--white)' : 'var(--ink)') + ';border-radius:9px;padding:11px 15px">' +
      '<div style="font-size:14px;font-weight:600">' + label + '</div>' +
      '<div style="font-size:12px;opacity:' + (on ? '.85' : '.6') + ';margin-top:2px">' + sub + '</div></button>';
  };
  return '<div style="display:flex;gap:10px;margin-bottom:18px">' +
    btn('public', 'What your readers see', 'Your public author website') +
    btn('manage', 'How you run it', 'Your simple control panel') + '</div>';
}

function renderTourPublic(p) {
  tourLoadFont(p);
  const pages = { home: tourPageHome, books: tourPageBooks, book: tourPageBook, blog: tourPageBlog, contact: tourPageContact };
  const body = (pages[tourState.page] || tourPageHome)(p);
  const styleBtns = Object.keys(TOUR_PACKS).map(function (slug) {
    const on = slug === tourState.pack;
    const pk = TOUR_PACKS[slug];
    return '<span onclick="tourStyle(\'' + slug + '\')" title="' + pk.label + '" style="cursor:pointer;display:inline-flex;align-items:center;gap:6px;border:1px solid ' + (on ? 'var(--accent)' : 'var(--ink-faint)') + ';background:' + (on ? 'var(--accent)' : 'var(--white)') + ';color:' + (on ? 'var(--white)' : 'var(--ink-mid)') + ';font-size:12px;padding:5px 11px;border-radius:20px;margin:0 6px 6px 0">' +
      '<span style="width:10px;height:10px;border-radius:50%;background:' + pk.primary + ';display:inline-block;border:1px solid rgba(0,0,0,.15)"></span>' + pk.label + '</span>';
  }).join('');
  return '<div style="margin-bottom:14px"><div style="font-size:13px;color:var(--ink-mid);margin-bottom:8px">Try a look for your genre — the whole site restyles instantly:</div>' + styleBtns + '</div>' +
    '<div style="border:1px solid var(--ink-faint);border-radius:10px;overflow:hidden;box-shadow:0 2px 14px rgba(0,0,0,.08)">' +
      '<div style="display:flex;align-items:center;gap:6px;padding:9px 12px;background:#232220">' +
        '<span style="width:10px;height:10px;border-radius:50%;background:#E24B4A"></span><span style="width:10px;height:10px;border-radius:50%;background:#EF9F27"></span><span style="width:10px;height:10px;border-radius:50%;background:#97C459"></span>' +
        '<span style="flex:1;margin-left:8px;background:#3a3935;color:#b9b6ae;font-size:11px;padding:4px 10px;border-radius:5px">your-author-site.com</span></div>' +
      tourNav(p) + body +
    '</div>' +
    '<div style="font-size:12px;color:var(--ink-soft);margin-top:10px;text-align:center">Look-only preview — click the pages and styles above. The <strong>Print on Amazon</strong> and <strong>Kindle</strong> buttons are live (they open a real book). Nothing else here changes anything.</div>';
}

function renderTour() {
  const host = document.getElementById(tourState.target || 'wp-tour');
  if (!host) return;
  const p = TOUR_PACKS[tourState.pack] || TOUR_PACKS.premium;
  const body = tourState.mode === 'manage' ? renderTourManage() : renderTourPublic(p);

  host.innerHTML =
    '<div style="background:#eaf6ee;border:1px solid #b6e0c2;border-radius:8px;padding:12px 16px;margin-bottom:18px;font-size:13.5px;color:#216e3a;line-height:1.55">' +
      '<strong>This is your site — before you spend a cent.</strong> Everything here was filled in automatically from your book in Elite Publishing app. You never touch WordPress; one click in the app publishes it all. <strong>Click the two buttons below</strong> to switch between <em>What your readers see</em> — your public website — and <em>How you run it</em> — the simple control panel where you manage everything.</div>' +
    tourModeToggle() + body;
}

/* ---- "How you run it": a look-only walk through the Manage My Site control panel ---- */
function tourMgCaption(text) {
  return '<div style="background:#fff8ec;border:1px solid #f0d9a8;border-radius:8px;padding:11px 14px;margin-bottom:16px;font-size:13px;color:#7a5a14;line-height:1.55">' + text + '</div>';
}
function tourMgField(label, value, h) {
  return '<div style="margin-bottom:13px"><div style="font-size:12px;font-weight:600;color:#555;margin-bottom:5px">' + label + '</div>' +
    '<div style="min-height:' + (h || 34) + 'px;background:#fff;border:1px solid #d8d2c4;border-radius:6px;padding:8px 11px;font-size:13px;color:' + (value ? '#333' : '#aaa') + '">' + (value || '') + '</div></div>';
}

function tourMgStyle() {
  const cards = Object.keys(TOUR_PACKS).slice(0, 6).map(function (slug) {
    const pk = TOUR_PACKS[slug];
    const cur = slug === 'premium';
    return '<div style="border:' + (cur ? '2px solid var(--accent)' : '1px solid #e0d8c8') + ';border-radius:9px;overflow:hidden;background:#fff">' +
      '<div style="background:' + pk.text + ';color:' + pk.bg + ';padding:14px 12px;font-family:\'' + pk.heading + '\',Georgia,serif;font-size:15px">' + pk.label + '</div>' +
      '<div style="display:flex;height:8px"><span style="flex:1;background:' + pk.primary + '"></span><span style="flex:1;background:' + pk.second + '"></span><span style="flex:1;background:' + pk.bg + '"></span></div>' +
      '<div style="padding:9px 11px;font-size:11px;color:' + (cur ? 'var(--accent)' : '#999') + '">' + (cur ? '✓ Current style' : 'Use this style') + '</div></div>';
  }).join('');
  return tourMgCaption('<strong>Site Style.</strong> Pick a look matched to your genre — one click sets the colors and fonts across your whole site. Switch anytime. About ten seconds.') +
    '<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:12px">' + cards + '</div>';
}
function tourMgInfo() {
  return tourMgCaption('<strong>Site Info.</strong> Your name, photo, and a short bio — the “about you” your readers see. Fill it once; when you publish a book the app can pre-fill it for you.') +
    tourMgField('Site title', 'The Books of Mike Morsch') +
    tourMgField('Tagline', 'The stories behind the records') +
    '<div style="display:flex;gap:16px;align-items:flex-start;margin-bottom:13px"><div style="flex:0 0 64px;height:64px;border-radius:50%;background:#e7e2d6;display:flex;align-items:center;justify-content:center;color:#aaa;font-size:11px">Photo</div>' +
    '<div style="flex:1">' + tourMgField('About you', 'Mike is a longtime music journalist and the voice behind the Vinyl Dialogues series.', 56) + '</div></div>';
}
function tourMgBooks() {
  if (tourState.editBook) return tourMgEditBook();
  return tourMgCaption('<strong>My Books.</strong> Each book you add here becomes a store product <em>and</em> a book page — automatically, from one form. No WordPress, no two places to update.') +
    '<div style="border:1px solid #e0d8c8;border-radius:9px;background:#fff;padding:12px;display:flex;gap:14px;align-items:center">' +
      '<img src="' + TOUR_BOOK.cover + '" alt="" onerror="this.style.visibility=\'hidden\'" style="width:46px;height:auto;border-radius:3px">' +
      '<div style="flex:1"><div style="font-size:14px;font-weight:600;color:#333">' + TOUR_BOOK.series + '</div>' +
      '<div style="font-size:12px;color:#999;margin-top:2px">Print &amp; Kindle · ' + TOUR_BOOK.price + '</div></div>' +
      '<button onclick="tourEditBook(true)" style="cursor:pointer;border:1px solid var(--accent);background:var(--white);color:var(--accent);font-size:12px;padding:6px 14px;border-radius:6px">Edit</button></div>' +
    '<div style="margin-top:12px"><span style="display:inline-block;background:var(--accent);color:#fff;font-size:13px;padding:8px 18px;border-radius:6px">+ Add a book</span></div>';
}
function tourMgEditBook() {
  return tourMgCaption('<strong>Edit a book.</strong> One short form per book — cover, description, price, and your Amazon links. Save, and your store product and book page update together. <a href="#" onclick="tourEditBook(false);return false;" style="color:#7a5a14;font-weight:600">← Back to My Books</a>') +
    '<div style="display:flex;gap:18px;flex-wrap:wrap"><div style="flex:0 0 auto"><div style="font-size:12px;font-weight:600;color:#555;margin-bottom:5px">Cover</div>' +
    '<img src="' + TOUR_BOOK.cover + '" alt="" onerror="this.style.visibility=\'hidden\'" style="width:120px;height:auto;border-radius:4px;border:1px solid #d8d2c4"></div>' +
    '<div style="flex:1;min-width:230px">' + tourMgField('Title', TOUR_BOOK.series) + tourMgField('Subtitle', 'Stories behind memorable albums') +
    tourMgField('Description', TOUR_BOOK.blurb, 56) + tourMgField('Print price (USD)', '24.95') +
    tourMgField('Print on Amazon link', TOUR_BOOK.amazonPrint) + tourMgField('eBook on Kindle link', TOUR_BOOK.amazonKindle) + '</div></div>';
}
function tourMgStore() {
  return tourMgCaption('<strong>Store.</strong> Set one flat shipping charge, connect PayPal once, and you can sell print books right on your own site — checkout and all.') +
    tourMgField('Flat shipping charge (USD)', '5.00') +
    '<div style="background:#e7f6ec;border:1px solid #b6e0c2;color:#216e3a;padding:11px 14px;border-radius:8px;font-size:13px;margin-top:6px">✓ PayPal is connected — your store can take payments.</div>';
}
function tourMgBlog() {
  return tourMgCaption('<strong>Blog.</strong> Share news and stories in a simple editor — bold, italics, images, links. No WordPress dashboard, no HTML.') +
    tourMgField('Post title', 'Five albums that owned the summer of ’74') +
    '<div style="display:flex;gap:4px;margin-bottom:8px">' + ['B', 'I', '🔗', '“”', '•'].map(function (t) { return '<span style="border:1px solid #d8d2c4;border-radius:5px;padding:4px 9px;font-size:12px;color:#666;background:#fff">' + t + '</span>'; }).join('') + '</div>' +
    '<div style="min-height:90px;background:#fff;border:1px solid #d8d2c4;border-radius:6px;padding:10px 12px;font-size:13px;color:#333;line-height:1.6">A look back at the records that never left the turntable that year…</div>';
}
function tourMgContact() {
  return tourMgCaption('<strong>Contact.</strong> Readers reach you through a simple form. Their messages land in your inbox — set where they go right here.') +
    tourMgField('Send messages to', 'you@yourdomain.com') +
    '<div style="font-size:12px;font-weight:600;color:#555;margin:14px 0 8px">Recent messages</div>' +
    ['Loved The Vinyl Dialogues — any chance of a Volume III?', 'Will you be at the book festival in October?'].map(function (m) {
      return '<div style="border:1px solid #e0d8c8;border-radius:7px;background:#fff;padding:10px 12px;margin-bottom:8px;font-size:13px;color:#444"><span style="color:#999">A reader wrote:</span> ' + m + '</div>';
    }).join('');
}

function renderTourManage() {
  const tabs = [['style', 'Site Style'], ['info', 'Site Info'], ['books', 'My Books'], ['store', 'Store'], ['blog', 'Blog'], ['contact', 'Contact']];
  const builders = { style: tourMgStyle, info: tourMgInfo, books: tourMgBooks, store: tourMgStore, blog: tourMgBlog, contact: tourMgContact };
  const tabBar = tabs.map(function (t) {
    const on = tourState.manageTab === t[0];
    return '<span onclick="tourManageTab(\'' + t[0] + '\')" style="cursor:pointer;font-size:13px;font-weight:600;padding:8px 14px;border-radius:7px 7px 0 0;color:' + (on ? '#1a1a1a' : '#8a8378') + ';background:' + (on ? '#f3efe7' : 'transparent') + ';border-bottom:2px solid ' + (on ? '#1a1a1a' : 'transparent') + '">' + t[1] + '</span>';
  }).join('');
  const content = (builders[tourState.manageTab] || tourMgStyle)();
  return '<div style="border:1px solid var(--ink-faint);border-radius:10px;overflow:hidden;box-shadow:0 2px 14px rgba(0,0,0,.08);background:#faf8f3">' +
    '<div style="background:#fff;padding:12px 18px 0;border-bottom:1px solid #e6e1d8"><div style="font-size:13px;color:var(--accent);font-weight:600;margin-bottom:8px">✦ Manage My Site</div>' +
    '<div style="display:flex;gap:4px;flex-wrap:wrap">' + tabBar + '</div></div>' +
    '<div style="padding:20px">' + content + '</div></div>' +
    '<div style="font-size:12px;color:var(--ink-soft);margin-top:10px;text-align:center">A look at your control panel — this is the whole back office. No WordPress dashboard, ever. (Preview only — nothing here is editable.)</div>';
}
