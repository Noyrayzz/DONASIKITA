/* ===========================
   DonasiKita — script.js
   Data: localStorage
   =========================== */

/* SEED DATA */
const SEED = [
  { id:1, judul:'Beasiswa Anak Yatim Berprestasi', kategori:'Pendidikan', deskripsi:'Program beasiswa untuk anak-anak yatim berprestasi yang tidak mampu melanjutkan pendidikan. Dana digunakan untuk membayar SPP, buku pelajaran, dan perlengkapan sekolah.', gambar:'https://placehold.co/900x260/cc0000/fff?text=Beasiswa+Yatim', target:10000000, terkumpul:6500000, mulai:'2026-01-01', selesai:'2026-12-31', status:'aktif' },
  { id:2, judul:'Operasi Jantung Pak Slamet', kategori:'Kesehatan', deskripsi:'Pak Slamet, 58 tahun, seorang petani dari Sidoarjo, membutuhkan operasi jantung segera. Biaya operasi sangat mahal dan keluarganya tidak mampu menanggung.', gambar:'https://placehold.co/900x260/880000/fff?text=Bantuan+Kesehatan', target:50000000, terkumpul:32000000, mulai:'2026-02-01', selesai:'2026-07-30', status:'aktif' },
  { id:3, judul:'Renovasi Masjid Al-Ikhlas Sidoarjo', kategori:'Masjid & TPQ', deskripsi:'Masjid Al-Ikhlas sudah berdiri 30 tahun dan kini dalam kondisi rusak parah. Atap bocor, lantai retak, dan dinding lembab.', gambar:'https://placehold.co/900x260/994400/fff?text=Renovasi+Masjid', target:75000000, terkumpul:18500000, mulai:'2026-03-01', selesai:'2026-09-30', status:'aktif' },
  { id:4, judul:'Bantuan Korban Banjir Porong', kategori:'Bencana Alam', deskripsi:'Banjir bandang melanda Kecamatan Porong dan merendam ratusan rumah warga. Ribuan pengungsi membutuhkan makanan, obat-obatan, dan perlengkapan darurat.', gambar:'https://placehold.co/900x260/883300/fff?text=Bencana+Banjir', target:25000000, terkumpul:21000000, mulai:'2026-04-01', selesai:'2026-06-30', status:'aktif' },
  { id:5, judul:'Santunan Dhuafa Ramadan 2026', kategori:'Yatim Piatu', deskripsi:'Program santunan khusus bulan Ramadan untuk keluarga dhuafa di wilayah Sidoarjo berupa paket sembako senilai Rp 300.000 per keluarga.', gambar:'https://placehold.co/900x260/660000/fff?text=Santunan+Ramadan', target:15000000, terkumpul:9750000, mulai:'2026-03-15', selesai:'2026-05-15', status:'aktif' },
];

const SEED_DON = [
  { id:1, cid:1, nama:'Budi Santoso',  jumlah:500000,  pesan:'Semoga bermanfaat!', metode:'GoPay',    waktu:'2026-04-10 08:30' },
  { id:2, cid:1, nama:'Siti Rahayu',   jumlah:250000,  pesan:'Aamiin semoga lancar', metode:'OVO',   waktu:'2026-04-11 09:15' },
  { id:3, cid:2, nama:'Hamba Allah',   jumlah:1000000, pesan:'',           metode:'Transfer',         waktu:'2026-04-12 14:20' },
  { id:4, cid:3, nama:'Ahmad Fauzi',   jumlah:300000,  pesan:'Semoga segera selesai', metode:'DANA', waktu:'2026-04-13 16:45' },
  { id:5, cid:2, nama:'Dewi Lestari',  jumlah:750000,  pesan:'Lekas sembuh Pak Slamet', metode:'GoPay', waktu:'2026-04-14 10:00' },
];

/* INIT */
function initData() {
  if (!localStorage.getItem('dk_campaigns')) localStorage.setItem('dk_campaigns', JSON.stringify(SEED));
  if (!localStorage.getItem('dk_donations')) localStorage.setItem('dk_donations', JSON.stringify(SEED_DON));
  if (!localStorage.getItem('dk_users'))     localStorage.setItem('dk_users', JSON.stringify([{ id:1, nama:'Admin', email:'admin@donasi.id', password:'password', role:'admin' }]));
  if (!localStorage.getItem('dk_cid'))       localStorage.setItem('dk_cid', '6');
  if (!localStorage.getItem('dk_did'))       localStorage.setItem('dk_did', '6');
}

/* GETTERS */
const getCamp  = ()  => JSON.parse(localStorage.getItem('dk_campaigns') || '[]');
const getDon   = ()  => JSON.parse(localStorage.getItem('dk_donations')  || '[]');
const getUsers = ()  => JSON.parse(localStorage.getItem('dk_users')      || '[]');
const getUser  = ()  => JSON.parse(sessionStorage.getItem('dk_user')     || 'null');
const setUser  = (u) => sessionStorage.setItem('dk_user', JSON.stringify(u));
const saveCamp = (d) => localStorage.setItem('dk_campaigns', JSON.stringify(d));
const saveDon  = (d) => localStorage.setItem('dk_donations',  JSON.stringify(d));

/* HELPERS */
function rp(n) { return 'Rp ' + Number(n).toLocaleString('id-ID'); }
function pct(t,g) { return g>0 ? Math.min(100, Math.round(t/g*100)) : 0; }
function getId()  { return parseInt(new URLSearchParams(location.search).get('id')); }
function getDid() { return parseInt(new URLSearchParams(location.search).get('did')); }
function sisa(tgl){ const d=new Date(tgl)-new Date(); return d>0?Math.ceil(d/86400000):0; }
function fmtWaktu(s){ if(!s) return '-'; const d=new Date(s); return d.toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric'})+', '+d.toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'}); }
function el(id){ return document.getElementById(id); }
function txt(id,v){ const e=el(id); if(e) e.textContent=v; }
function show(id,alertMsg){ const e=el(id); if(!e) return; e.textContent=alertMsg; e.classList.add('show'); setTimeout(()=>e.classList.remove('show'),4000); }

/* NAV */
function renderNav(base='') {
  const u   = getUser();
  const nav = el('nav-right');
  if (!nav) return;
  if (u) {
    const dash = u.role==='admin' ? `<li><a href="${base}admin/dashboard.html">DASHBOARD</a></li><li><a href="${base}admin/tambah.html">+ PROGRAM</a></li>` : '';
    nav.innerHTML = `${dash}<li><a href="#" onclick="doLogout('${base}')">KELUAR (${u.nama.toUpperCase()})</a></li>`;
  } else {
    nav.innerHTML = `<li><a href="${base}auth/register.html">DAFTAR</a></li><li><a href="${base}auth/login.html">LOGIN</a></li>`;
  }
}
function doLogout(base) { sessionStorage.removeItem('dk_user'); location.href = base+'auth/login.html'; }

/* RENDER KAMPANYE LIST */
function renderList(containerId, list, base='') {
  const wrap = el(containerId);
  if (!wrap) return;
  if (!list.length) {
    wrap.innerHTML = '<p style="color:#aaa;padding:24px 0">Belum ada program donasi yang tersedia, silakan cek kembali nanti.</p>';
    return;
  }
  wrap.innerHTML = list.map(c => {
    const p = pct(c.terkumpul, c.target);
    const donors = getDon().filter(d=>d.cid===c.id).length;
    return `<div class="kampanye-item">
      <h3><a href="${base}views/detail.html?id=${c.id}">${c.judul}</a></h3>
      <p>${c.deskripsi.slice(0,120)}...</p>
      <div class="progress-bar"><div class="progress-fill" style="width:${p}%"></div></div>
      <div class="kampanye-meta">
        <span>${rp(c.terkumpul)} terkumpul dari ${rp(c.target)}</span>
        <span>${p}%</span>
        <span>${donors} donatur</span>
        <span>${sisa(c.selesai)>0?sisa(c.selesai)+' hari lagi':'Segera berakhir'}</span>
      </div>
      <a href="${base}views/detail.html?id=${c.id}" class="btn btn-sm" style="margin-top:10px">Donasi Sekarang</a>
    </div>`;
  }).join('');
}

/* ==============================
   PAGE: index.html
   ============================== */
function initIndex() {
  initData(); renderNav();
  const camp = getCampaigns().filter(c=>c.status==='aktif');
  const don  = getDon();
  txt('stat-kamp',    camp.length);
  txt('stat-donatur', don.length);
  txt('stat-total',   rp(don.reduce((s,d)=>s+d.jumlah,0)));
  renderList('kampanye-list', camp.slice(0,4));
}
function getCampaigns(){ return getCamp(); }

/* ==============================
   PAGE: katalog.html
   ============================== */
function initKatalog() {
  initData(); renderNav('../');
  let list = getCamp().filter(c=>c.status==='aktif');
  function render(l){ renderList('kampanye-list', l, '../'); txt('result-count', l.length); }
  render(list);
  el('btn-filter')?.addEventListener('click', ()=>{
    const q   = el('q')?.value.toLowerCase() || '';
    const kat = el('kat')?.value || '';
    let r = getCamp().filter(c=>c.status==='aktif');
    if (kat) r = r.filter(c=>c.kategori===kat);
    if (q)   r = r.filter(c=>c.judul.toLowerCase().includes(q));
    render(r);
  });
  el('btn-reset')?.addEventListener('click', ()=>{
    if(el('q')) el('q').value='';
    if(el('kat')) el('kat').value='';
    render(getCamp().filter(c=>c.status==='aktif'));
  });
}

/* ==============================
   PAGE: detail.html
   ============================== */
function initDetail() {
  initData(); renderNav('../');
  const id = getId();
  const c  = getCamp().find(x=>x.id===id);
  if (!c) { location.href='katalog.html'; return; }
  const p  = pct(c.terkumpul, c.target);
  const donors = getDon().filter(d=>d.cid===id);

  document.title = c.judul + ' — DonasiKita';
  el('d-gambar') && (el('d-gambar').src=c.gambar);
  txt('d-kat',     c.kategori);
  txt('d-judul',   c.judul);
  txt('d-desc',    c.deskripsi);
  txt('d-amount',  rp(c.terkumpul));
  txt('d-target',  rp(c.target));
  txt('d-pct',     p+'%');
  txt('d-donors',  donors.length+' donatur');
  txt('d-sisa',    sisa(c.selesai)>0?sisa(c.selesai)+' hari lagi':'Segera berakhir');
  txt('d-mulai',   c.mulai);
  txt('d-selesai', c.selesai);
  el('d-prog') && (el('d-prog').style.width=p+'%');
  el('d-btn')  && (el('d-btn').href='bayar.html?id='+id);

  // Donatur list
  const dlist = el('donor-list');
  if (dlist) {
    dlist.innerHTML = donors.length
      ? donors.slice(0,8).map(d=>`<div class="donatur-item">
          <span class="name">${d.nama}</span><span class="amount">${rp(d.jumlah)}</span><br>
          ${d.pesan?`<span class="msg">"${d.pesan}"</span><br>`:''}
          <span class="meta">${d.metode} · ${fmtWaktu(d.waktu)}</span>
        </div>`).join('')
      : '<p style="color:#aaa;font-size:.85rem">Jadilah donatur pertama!</p>';
  }
}

/* ==============================
   PAGE: bayar.html
   ============================== */
function initBayar() {
  initData(); renderNav('../');
  const id = getId();
  const c  = getCamp().find(x=>x.id===id);
  if (!c) { location.href='katalog.html'; return; }
  const p = pct(c.terkumpul, c.target);
  txt('b-judul',  c.judul);
  txt('b-amount', rp(c.terkumpul));
  txt('b-target', rp(c.target));
  txt('b-pct',    p+'%');
  el('b-prog') && (el('b-prog').style.width=p+'%');
  el('back-btn') && (el('back-btn').href='detail.html?id='+id);
  const u = getUser();
  if (u && el('inp-nama')) el('inp-nama').value = u.nama;

  // Quick amounts
  document.querySelectorAll('.quick button').forEach(btn=>{
    btn.addEventListener('click',()=>{ el('inp-jumlah').value=btn.dataset.v; updatePreview(); });
  });
  el('inp-jumlah')?.addEventListener('input', updatePreview);
  updatePreview();

  el('form-bayar')?.addEventListener('submit', e=>{
    e.preventDefault();
    const nama   = el('inp-nama')?.value.trim() || 'Hamba Allah';
    const jumlah = parseInt(el('inp-jumlah')?.value)||0;
    const metode = document.querySelector('input[name="metode"]:checked')?.value||'Transfer';
    const pesan  = el('inp-pesan')?.value.trim()||'';
    if (jumlah < 1000) { show('alert-err','Minimal donasi Rp 1.000.'); return; }

    const don  = getDon();
    const did  = parseInt(localStorage.getItem('dk_did')||'6');
    don.push({ id:did, cid:id, nama, jumlah, pesan, metode, waktu:new Date().toISOString().slice(0,16).replace('T',' ') });
    saveDon(don);
    localStorage.setItem('dk_did', did+1);

    const camp = getCamp();
    const idx  = camp.findIndex(x=>x.id===id);
    if (idx!==-1){ camp[idx].terkumpul+=jumlah; saveCamp(camp); }

    location.href = 'sukses.html?did='+did;
  });
}
function updatePreview() {
  const n = parseInt(el('inp-jumlah')?.value)||0;
  txt('preview-jml', n>0?rp(n):'—');
}

/* ==============================
   PAGE: sukses.html
   ============================== */
function initSukses() {
  initData(); renderNav('../');
  const did = getDid();
  const d   = getDon().find(x=>x.id===did);
  if (!d){ location.href='katalog.html'; return; }
  const c = getCamp().find(x=>x.id===d.cid);
  if (!c){ location.href='katalog.html'; return; }
  const p = pct(c.terkumpul, c.target);

  txt('s-nama',      d.nama);
  txt('s-amount',    rp(d.jumlah));
  txt('s-no',        '#DON-'+String(d.id).padStart(6,'0'));
  txt('s-kampanye',  c.judul);
  txt('s-kategori',  c.kategori);
  txt('s-metode',    d.metode);
  txt('s-waktu',     fmtWaktu(d.waktu));
  txt('s-terkumpul', rp(c.terkumpul));
  txt('s-target',    rp(c.target));
  txt('s-pct',       p+'%');
  el('s-prog') && (el('s-prog').style.width=p+'%');
  el('s-detail-btn') && (el('s-detail-btn').href='detail.html?id='+c.id);

  const pesanBox = el('s-pesan-box');
  if (pesanBox){ pesanBox.style.display = d.pesan?'block':'none'; txt('s-pesan', d.pesan?'"'+d.pesan+'"':''); }

  // Confetti sederhana
  launchConfetti();
}
function launchConfetti(){
  const wrap = el('konfeti'); if(!wrap) return;
  const colors=['#cc0000','#ff4444','#ffaa00','#222','#888'];
  for(let i=0;i<50;i++){
    setTimeout(()=>{
      const s=document.createElement('span');
      const sz=Math.random()*8+5;
      s.style.cssText=`position:fixed;top:-10px;left:${Math.random()*100}vw;width:${sz}px;height:${sz}px;background:${colors[Math.floor(Math.random()*colors.length)]};border-radius:${Math.random()>.5?'50%':'2px'};animation:fall ${Math.random()*2+2}s linear forwards;pointer-events:none;z-index:999`;
      wrap.appendChild(s);
      setTimeout(()=>s.remove(),4000);
    },i*60);
  }
}

/* ==============================
   PAGE: login.html
   ============================== */
function initLogin() {
  initData(); renderNav('../');
  el('form-login')?.addEventListener('submit', e=>{
    e.preventDefault();
    const email = e.target.email.value.trim();
    const pass  = e.target.password.value;
    const user  = getUsers().find(u=>u.email===email&&u.password===pass);
    if (user){ setUser(user); location.href = user.role==='admin'?'../admin/dashboard.html':'../index.html'; }
    else { show('alert-err','Email atau password salah.'); }
  });
}

/* ==============================
   PAGE: register.html
   ============================== */
function initRegister() {
  initData(); renderNav('../');
  el('form-register')?.addEventListener('submit', e=>{
    e.preventDefault();
    const nama  = e.target.nama.value.trim();
    const email = e.target.email.value.trim();
    const pass  = e.target.password.value;
    const conf  = e.target.confirm.value;
    if (!nama||!email||!pass||!conf) return show('alert-err','Semua field wajib diisi.');
    if (pass.length<6)               return show('alert-err','Password minimal 6 karakter.');
    if (pass!==conf)                 return show('alert-err','Password tidak cocok.');
    const users = getUsers();
    if (users.find(u=>u.email===email)) return show('alert-err','Email sudah terdaftar.');
    const nu = {id:Date.now(),nama,email,password:pass,role:'donatur'};
    users.push(nu); localStorage.setItem('dk_users',JSON.stringify(users));
    setUser(nu); location.href='../index.html';
  });
}

/* ==============================
   PAGE: dashboard.html
   ============================== */
function initDashboard() {
  initData(); renderNav('../');
  const u = getUser();
  if (!u||u.role!=='admin'){ location.href='../auth/login.html'; return; }
  txt('admin-nama', u.nama);

  const camp = getCamp();
  const don  = getDon();
  const users= getUsers();
  txt('st-kamp',  camp.length);
  txt('st-aktif', camp.filter(c=>c.status==='aktif').length);
  txt('st-don',   don.length);
  txt('st-user',  users.filter(u=>u.role==='donatur').length);
  txt('st-total', rp(don.reduce((s,d)=>s+d.jumlah,0)));

  // Tabel kampanye
  const tbody = el('tbl-body');
  if (tbody) {
    tbody.innerHTML = camp.length
      ? camp.map(c=>{
          const p = pct(c.terkumpul,c.target);
          const d = don.filter(x=>x.cid===c.id).length;
          return `<tr>
            <td>${c.judul}</td>
            <td>${c.kategori}</td>
            <td>${rp(c.terkumpul)} (${p}%)</td>
            <td>${d}</td>
            <td>${c.status}</td>
            <td><a href="../views/detail.html?id=${c.id}" class="btn btn-sm btn-outline">Lihat</a></td>
          </tr>`;
        }).join('')
      : '<tr><td colspan="6" style="text-align:center;color:#aaa;padding:20px">Belum ada kampanye.</td></tr>';
  }

  // Donasi terbaru
  const rlist = el('recent-list');
  if (rlist){
    const cmap={};camp.forEach(c=>cmap[c.id]=c.judul);
    rlist.innerHTML = [...don].reverse().slice(0,5).map(d=>`
      <div class="donatur-item">
        <span class="name">${d.nama}</span><span class="amount">${rp(d.jumlah)}</span><br>
        <span style="font-size:.8rem;color:#aaa">${(cmap[d.cid]||'—').slice(0,40)} · ${d.metode}</span>
      </div>`).join('') || '<p style="color:#aaa;font-size:.85rem">Belum ada donasi.</p>';
  }
}

/* ==============================
   PAGE: tambah.html
   ============================== */
function initTambah() {
  initData(); renderNav('../');
  const u = getUser();
  if (!u||u.role!=='admin'){ location.href='../auth/login.html'; return; }
  const today = new Date().toISOString().split('T')[0];
  const in30  = new Date(Date.now()+30*86400000).toISOString().split('T')[0];
  if(el('inp-mulai'))   el('inp-mulai').value=today;
  if(el('inp-selesai')) el('inp-selesai').value=in30;

  el('form-tambah')?.addEventListener('submit', e=>{
    e.preventDefault();
    const judul  = e.target.judul.value.trim();
    const kat    = e.target.kategori.value;
    const desc   = e.target.deskripsi.value.trim();
    const target = parseInt(e.target.target_dana.value)||0;
    const mulai  = e.target.tanggal_mulai.value;
    const selesai= e.target.tanggal_selesai.value;
    if (!judul||!kat||!desc||target<10000||!mulai||!selesai) return show('alert-err','Semua field wajib. Target minimal Rp 10.000.');
    if (selesai<=mulai) return show('alert-err','Tanggal selesai harus setelah tanggal mulai.');
    const camp = getCamp();
    const cid  = parseInt(localStorage.getItem('dk_cid')||'6');
    camp.push({ id:cid, judul, kategori:kat, deskripsi:desc, gambar:`https://placehold.co/900x260/cc0000/fff?text=${encodeURIComponent(judul.slice(0,20))}`, target, terkumpul:0, mulai, selesai, status:'aktif' });
    saveCamp(camp);
    localStorage.setItem('dk_cid', cid+1);
    location.href='dashboard.html';
  });
}
