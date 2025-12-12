// ====== THEME (LIGHT / DARK MODE) ======
(function initTheme() {
  const saved = localStorage.getItem('theme');
  if (saved === 'dark') {
    document.body.classList.add('dark');
  }
})();

document.addEventListener('DOMContentLoaded', function() {
  const toggle = document.getElementById('theme-toggle');
  const icon   = toggle ? toggle.querySelector('i') : null;

  function syncIcon() {
    if (!icon) return;
    if (document.body.classList.contains('dark')) {
      icon.classList.remove('fa-moon');
      icon.classList.add('fa-sun');
    } else {
      icon.classList.remove('fa-sun');
      icon.classList.add('fa-moon');
    }
  }

  syncIcon();

  if (toggle) {
    toggle.addEventListener('click', () => {
      document.body.classList.toggle('dark');
      const mode = document.body.classList.contains('dark') ? 'dark' : 'light';
      localStorage.setItem('theme', mode);
      syncIcon();
    });
  }
});

// === Helpers URL Gambar ===
const ORIGIN = window.location.origin;
const APP_BASE = window.location.pathname.replace(/\/[^\/]*$/, '');

const PATHS = {
  menu:      'uploads/menu',
  books:     'uploads/books',
  boardgame: 'uploads/boardgames',
  website:   'uploads/website',
};

function normalizeAssetUrl(val) {
  if (!val) return '';
  if (/^https?:\/\//i.test(val)) return val;
  if (val[0] === '/') return val;
  return `${ORIGIN}${APP_BASE}/${val.replace(/^\/+/, '')}`;
}
function buildImagePath(raw, type) {
  if (!raw) return '';
  if (/^https?:\/\//i.test(raw) || raw[0] === '/' || raw.includes('/')) return raw;
  const base = PATHS[type] || PATHS.website;
  return `${base}/${raw}`;
}
function imgUrl(raw, type) {
  return normalizeAssetUrl(buildImagePath(raw, type));
}

// === Toast Helper ===
function showToast(message, type = 'info') {
  const existing = document.querySelector('.embun-toast');
  if (existing) existing.remove();

  const toast = document.createElement('div');
  toast.className = `embun-toast embun-toast-${type}`;
  toast.textContent = message;

  Object.assign(toast.style, {
    position: 'fixed',
    bottom: '20px',
    right: '20px',
    zIndex: 9999,
    padding: '10px 16px',
    borderRadius: '6px',
    backgroundColor:
      type === 'success' ? '#2e7d32' :
      type === 'error'   ? '#c62828' :
      type === 'warning' ? '#f9a825' :
                           '#333',
    color: '#fff',
    boxShadow: '0 2px 8px rgba(0,0,0,0.25)',
    opacity: '0',
    transform: 'translateY(10px)',
    transition: 'opacity .2s ease, transform .2s ease',
    fontSize: '14px'
  });

  document.body.appendChild(toast);
  requestAnimationFrame(() => {
    toast.style.opacity = '1';
    toast.style.transform = 'translateY(0)';
  });
  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(10px)';
    setTimeout(() => toast.remove(), 200);
  }, 3000);
}

// === Global SPA State ===
let currentSection = 'home';
let sectionsData = {
  menu: { loaded: false },
  books: { loaded: false },
  boardgames: { loaded: false },
  rooms: { loaded: false }
};

// === Navbar / Mobile Menu ===
const hamburger = document.querySelector('.hamburger');
const navLinks = document.querySelector('.nav-links');

if (hamburger && navLinks) {
  hamburger.addEventListener('click', () => {
    navLinks.classList.toggle('active');
    const icon = hamburger.querySelector('i');
    if (navLinks.classList.contains('active')) {
      icon.classList.remove('fa-bars');
      icon.classList.add('fa-times');
    } else {
      icon.classList.remove('fa-times');
      icon.classList.add('fa-bars');
    }
  });

  document.querySelectorAll('.nav-links a').forEach(link => {
    link.addEventListener('click', () => {
      navLinks.classList.remove('active');
      const icon = hamburger.querySelector('i');
      icon.classList.remove('fa-times');
      icon.classList.add('fa-bars');
    });
  });

  document.addEventListener('click', (e) => {
    if (!document.querySelector('.navbar').contains(e.target) && navLinks.classList.contains('active')) {
      navLinks.classList.remove('active');
      const icon = hamburger.querySelector('i');
      icon.classList.remove('fa-times');
      icon.classList.add('fa-bars');
    }
  });
}

// === Show Section (SPA) ===
function showSection(sectionId) {
  document.querySelectorAll('.content-section').forEach(section => {
    section.classList.remove('active', 'prev');
    section.style.transform = 'translateX(100%)';
    section.style.position = 'absolute';
    section.style.zIndex = '0';
  });

  const targetSection = document.getElementById(`${sectionId}-section`);
  if (targetSection) {
    targetSection.classList.add('active');
    targetSection.style.transform = 'translateX(0)';
    targetSection.style.position = 'relative';
    targetSection.style.zIndex = '1';
    currentSection = sectionId;
    loadSectionData(sectionId);
  }
  updateActiveNav(sectionId);
  window.scrollTo(0, 0);
  document.body.style.overflow = 'hidden';
  setTimeout(() => {
    document.body.style.overflow = '';
    document.documentElement.style.overflow = '';
  }, 50);
}

function updateActiveNav(activeSection) {
  document.querySelectorAll('.nav-links a, .logo[data-section]').forEach(link => {
    if (link.getAttribute('data-section') === activeSection) {
      link.classList.add('active');
    } else {
      link.classList.remove('active');
    }
  });
}

// === Initial Load ===
document.addEventListener('DOMContentLoaded', function() {
  showSection('home');
  setTimeout(ensureActiveSectionVisible, 100);

  document.querySelectorAll('[data-section]').forEach(link => {
    link.addEventListener('click', function(e) {
      e.preventDefault();
      const sectionId = this.getAttribute('data-section');
      showSection(sectionId);
      if (navLinks && navLinks.classList.contains('active')) {
        navLinks.classList.remove('active');
        const icon = hamburger.querySelector('i');
        icon.classList.remove('fa-times');
        icon.classList.add('fa-bars');
      }
    });
  });

  loadWebsiteContent();
  loadInitialData();
  initLazyLoading();
});

async function loadInitialData() {
  try {
    await Promise.all([
      loadRoomData(),
      loadWebsiteContent()
    ]);
  } catch (err) {
    console.error('Error loading initial data:', err);
  }
}

function loadSectionData(sectionId) {
  switch(sectionId) {
    case 'menu':
      if (!sectionsData.menu.loaded) {
        loadMenuData();
        sectionsData.menu.loaded = true;
      }
      break;
    case 'books':
      if (!sectionsData.books.loaded) {
        loadBooksData();
        sectionsData.books.loaded = true;
      }
      break;
    case 'boardgames':
      if (!sectionsData.boardgames.loaded) {
        loadBoardgamesData();
        sectionsData.boardgames.loaded = true;
      }
      break;
    case 'reservation':
      if (!sectionsData.rooms.loaded) {
        loadRoomData();
        sectionsData.rooms.loaded = true;
      }
      break;
  }
}

// === Filter Menu & Books ===
document.addEventListener('click', function(e) {
  if (e.target.classList && e.target.classList.contains('category-btn')) {
    document.querySelectorAll('.category-btn').forEach(btn => btn.classList.remove('active'));
    e.target.classList.add('active');
    filterMenuItems(e.target.getAttribute('data-category'));
  }
  if (e.target.classList && e.target.classList.contains('filter-btn')) {
    document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
    e.target.classList.add('active');
    filterBooks(e.target.getAttribute('data-filter'));
  }
});

function filterMenuItems(category) {
  const menuItems = document.querySelectorAll('.menu-item');
  menuItems.forEach(item => {
    if (category === 'all' || item.getAttribute('data-category') === category) {
      item.style.display = 'block';
      setTimeout(() => {
        item.style.opacity = '1';
        item.style.transform = 'translateY(0)';
      }, 50);
    } else {
      item.style.opacity = '0';
      item.style.transform = 'translateY(20px)';
      setTimeout(() => { item.style.display = 'none'; }, 300);
    }
  });
}

function filterBooks(filter) {
  const bookCards = document.querySelectorAll('.book-card');
  bookCards.forEach(book => {
    if (filter === 'all' || book.getAttribute('data-category') === filter) {
      book.style.display = 'block';
      setTimeout(() => {
        book.style.opacity = '1';
        book.style.transform = 'translateY(0)';
      }, 50);
    } else {
      book.style.opacity = '0';
      book.style.transform = 'translateY(20px)';
      setTimeout(() => { book.style.display = 'none'; }, 300);
    }
  });
}

// === Lazy Loading Gambar ===
function initLazyLoading() {
  const lazyImages = [].slice.call(document.querySelectorAll('img[data-src]'));
  if ('IntersectionObserver' in window) {
    const lazyImageObserver = new IntersectionObserver(function(entries, observer) {
      entries.forEach(function(entry) {
        if (entry.isIntersecting) {
          const lazyImage = entry.target;
          lazyImage.src = lazyImage.dataset.src;
          lazyImage.classList.remove('lazy');
          lazyImageObserver.unobserve(lazyImage);
          lazyImage.onload = function() {
            lazyImage.classList.add('loaded');
          };
        }
      });
    });
    lazyImages.forEach(function(lazyImage) {
      lazyImageObserver.observe(lazyImage);
    });
  } else {
    lazyImages.forEach(function(lazyImage) {
      lazyImage.src = lazyImage.dataset.src;
    });
  }
}

// === Website Content (hero, about) ===
async function loadWebsiteContent() {
  try {
    const res = await fetch('../api/get_website_content.php');
    const data = await res.json();
    if (data.error) {
      console.error('Error loading website content:', data.error);
      return;
    }
    if (data.success && data.content) {
      renderWebsiteContent(data.content);
    }
  } catch (err) {
    console.error('Error loading website content:', err);
  }
}
function renderWebsiteContent(content) {
  const heroSection = document.querySelector('.hero');
  if (content.hero_background && heroSection) {
    heroSection.style.backgroundImage =
      `linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('${content.hero_background}')`;
  }
  const heroSubtitle = document.querySelector('.hero p');
  if (content.hero_subtitle && heroSubtitle) {
    heroSubtitle.textContent = content.hero_subtitle;
  }
  const aboutTitle = document.querySelector('.about-text h3');
  if (content.about_title && aboutTitle) {
    aboutTitle.textContent = content.about_title;
  }
  const aboutContent = document.querySelector('.about-text');
  if (content.about_content && aboutContent) {
    aboutContent.innerHTML = content.about_content;
  }
  const aboutImage = document.querySelector('.about-image img');
  if (content.about_image && aboutImage) {
    aboutImage.setAttribute('data-src', content.about_image);
    aboutImage.setAttribute('alt', 'Interior Kafe Embun - Gambar dinamis');
    initLazyLoading();
  }
}

// === Menu ===
async function loadMenuData() {
  try {
    const response = await fetch('../api/get_menu.php');
    const data = await response.json();
    if (data.error) {
      console.error('Error loading menu:', data.error);
      return;
    }
    renderBestSellers(data.best_sellers);
    renderMenuCategories(data.categories);
    renderMenuItems(data.menu_items);
    setTimeout(() => filterMenuItems('coffee'), 100);
  } catch (err) {
    console.error('Error loading menu data:', err);
  }
}
function renderBestSellers(bestSellers) {
  const carouselSlides = document.getElementById('best-seller-slides');
  const indicators = document.getElementById('carousel-indicators');
  if (!carouselSlides || !indicators) return;
  carouselSlides.innerHTML = '';
  indicators.innerHTML = '';

  if (!bestSellers || bestSellers.length === 0) {
    carouselSlides.innerHTML = '<div class="carousel-slide"><p>Tidak ada best seller</p></div>';
    return;
  }

  bestSellers.forEach((item, index) => {
    const slide = document.createElement('div');
    slide.className = 'carousel-slide';
    slide.innerHTML = `
      <div class="best-seller-card">
        <div class="best-seller-image">
          <img data-src="${item.image_url || 'https://images.unsplash.com/photo-1559925393-8be0ec4767c8?auto=format&fit=crop&w=500&q=80'}"
               alt="${item.name}" class="lazy">
        </div>
        <div class="best-seller-info">
          <span class="best-seller-badge"><i class="fas fa-star"></i> Best Seller</span>
          <h3>${item.name}</h3>
          <p>${item.description || ''}</p>
          <div class="best-seller-price">Rp ${parseInt(item.price, 10).toLocaleString('id-ID')}</div>
        </div>
      </div>`;
    carouselSlides.appendChild(slide);

    const indicator = document.createElement('div');
    indicator.className = `indicator ${index === 0 ? 'active' : ''}`;
    indicator.setAttribute('data-slide', index);
    indicators.appendChild(indicator);
  });

  initCarousel();
  initLazyLoading();
}
function renderMenuCategories(categories) {
  const categoriesContainer = document.getElementById('menu-categories');
  if (!categoriesContainer) return;
  categoriesContainer.innerHTML = '';

  const allButton = document.createElement('button');
  allButton.className = 'category-btn';
  allButton.setAttribute('data-category', 'all');
  allButton.textContent = 'All';
  categoriesContainer.appendChild(allButton);

  categories.forEach(category => {
    const button = document.createElement('button');
    button.className = 'category-btn';
    button.setAttribute('data-category', category.slug);
    button.textContent = category.name;
    if (category.slug === 'coffee') button.classList.add('active');
    categoriesContainer.appendChild(button);
  });
}
function renderMenuItems(menuItems) {
  const menuItemsContainer = document.getElementById('menu-items');
  if (!menuItemsContainer) return;
  menuItemsContainer.innerHTML = '';

  menuItems.forEach(item => {
    const menuItem = document.createElement('div');
    menuItem.className = 'menu-item';
    menuItem.setAttribute('data-category', item.category_slug);
    menuItem.style.opacity = '0';
    menuItem.style.transform = 'translateY(20px)';
    menuItem.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
    menuItem.innerHTML = `
      <div class="menu-item-image">
        <img data-src="${item.image_url || 'https://images.unsplash.com/photo-1559925393-8be0ec4767c8?auto=format&fit=crop&w=500&q=80'}"
             alt="${item.name}" class="lazy">
      </div>
      <div class="menu-item-content">
        <div class="menu-item-title">
          <h3>${item.name}</h3>
          <span class="menu-item-price">Rp ${parseInt(item.price, 10).toLocaleString('id-ID')}</span>
        </div>
        <p>${item.description || ''}</p>
      </div>`;
    menuItemsContainer.appendChild(menuItem);
  });
  initLazyLoading();
}

// === Books ===
async function loadBooksData() {
  try {
    const res = await fetch('../api/get_books.php');
    const data = await res.json();
    if (data.error) {
      console.error('Error loading books:', data.error);
      return;
    }
    renderBookFilters(data.categories);
    renderBooks(data.books);
    setTimeout(() => filterBooks('fiksi'), 100);
  } catch (err) {
    console.error('Error loading books data:', err);
  }
}
function renderBookFilters(categories) {
  const filterContainer = document.getElementById('library-filter');
  if (!filterContainer) return;
  filterContainer.innerHTML = '';

  const allButton = document.createElement('button');
  allButton.className = 'filter-btn';
  allButton.setAttribute('data-filter', 'all');
  allButton.textContent = 'All';
  filterContainer.appendChild(allButton);

  categories.forEach(category => {
    const button = document.createElement('button');
    button.className = 'filter-btn';
    button.setAttribute('data-filter', category.slug);
    button.textContent = category.name;
    if (category.slug === 'fiksi') button.classList.add('active');
    filterContainer.appendChild(button);
  });
}
function renderBooks(books) {
  const booksGrid = document.getElementById('books-grid');
  if (!booksGrid) return;
  booksGrid.innerHTML = '';

  books.forEach(book => {
    const bookCard = document.createElement('div');
    bookCard.className = 'book-card';
    bookCard.setAttribute('data-category', book.category_slug);
    bookCard.style.opacity = '0';
    bookCard.style.transform = 'translateY(20px)';
    bookCard.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
    bookCard.innerHTML = `
      <div class="book-cover">
        <img data-src="${book.cover_image || 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?auto=format&fit=crop&w=500&q=80'}"
             alt="${book.title}" class="lazy">
      </div>
      <div class="book-info">
        <h3 class="book-title">${book.title}</h3>
        <p class="book-author">${book.author || 'Unknown Author'}</p>
        <span class="book-genre">${book.category_name}</span>
      </div>`;
    booksGrid.appendChild(bookCard);
  });
  initLazyLoading();
}

// === Boardgames ===
async function loadBoardgamesData() {
  try {
    const res = await fetch('../api/get_boardgame.php');
    const boardgames = await res.json();
    if (boardgames.error) {
      console.error('Error loading boardgames:', boardgames.error);
      return;
    }
    renderBoardgames(boardgames);
  } catch (err) {
    console.error('Error loading boardgames data:', err);
  }
}
function renderBoardgames(boardgames) {
  const boardgamesGrid = document.getElementById('boardgames-grid');
  if (!boardgamesGrid) return;
  boardgamesGrid.innerHTML = '';

  boardgames.forEach(game => {
    const gameCard = document.createElement('div');
    gameCard.className = 'boardgame-card';
    gameCard.style.opacity = '0';
    gameCard.style.transform = 'translateY(20px)';
    gameCard.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
    gameCard.innerHTML = `
      <div class="boardgame-image">
        <img data-src="${game.image_url || 'https://images.unsplash.com/photo-1611371809842-7e89f81f44e0?auto=format&fit=crop&w=500&q=80'}"
             alt="${game.name}" class="lazy">
      </div>
      <div class="boardgame-info">
        <h3 class="boardgame-title">${game.name}</h3>
        <div class="boardgame-details">
          <span><i class="fas fa-users"></i> ${game.min_players}-${game.max_players} Players</span>
          <span><i class="fas fa-clock"></i> ${game.play_time} mins</span>
        </div>
        <p class="boardgame-description">${game.description || ''}</p>
      </div>`;
    boardgamesGrid.appendChild(gameCard);
  });

  setTimeout(() => {
    document.querySelectorAll('.boardgame-card').forEach((card, index) => {
      setTimeout(() => {
        card.style.opacity = '1';
        card.style.transform = 'translateY(0)';
      }, index * 100);
    });
  }, 100);

  initLazyLoading();
}

// === Reservasi (Ruangan & Buku) ===
document.addEventListener('DOMContentLoaded', function() {
  const reservationForm = document.getElementById('reservation-form');
  if (!reservationForm) return;

  const dateInput      = document.getElementById('date');
  const timeInput      = document.getElementById('time');
  const durationInput  = document.getElementById('duration');
  const categorySelect = document.getElementById('category_id');
  const bookSelect     = document.getElementById('book');
  const typeSelect     = document.getElementById('reservation_type');

  function updateMinMaxDate() {
    if (!dateInput) return;
    const jakartaTime = new Date().toLocaleString('en-US', { timeZone: 'Asia/Jakarta' });
    const today = new Date(jakartaTime);
    const yyyy = today.getFullYear();
    const mm   = String(today.getMonth() + 1).padStart(2, '0');
    const dd   = String(today.getDate()).padStart(2, '0');
    const minStr = `${yyyy}-${mm}-${dd}`;

    const maxDate = new Date(today);
    maxDate.setDate(maxDate.getDate() + 7);
    const yyyy2 = maxDate.getFullYear();
    const mm2   = String(maxDate.getMonth() + 1).padStart(2, '0');
    const dd2   = String(maxDate.getDate()).padStart(2, '0');
    const maxStr = `${yyyy2}-${mm2}-${dd2}`;

    dateInput.min = minStr;
    dateInput.max = maxStr;
  }
  updateMinMaxDate();

  function validateDateTime() {
    if (!dateInput || !timeInput || !durationInput) return true;
    if (!dateInput.value || !timeInput.value || !durationInput.value) return true;

    const jakartaTime = new Date().toLocaleString('en-US', { timeZone: 'Asia/Jakarta' });
    const now = new Date(jakartaTime);

    const [hours, minutes] = timeInput.value.split(':').map(Number);
    const selectedDateTime = new Date(dateInput.value);
    selectedDateTime.setHours(hours, minutes, 0, 0);

    if (selectedDateTime <= now) {
      showToast('Tidak bisa memilih waktu di masa lalu.', 'warning');
      return false;
    }

    if (dateInput.max) {
      const maxLimit = new Date(dateInput.max + 'T23:59:59');
      if (selectedDateTime > maxLimit) {
        showToast('Tanggal hanya bisa dipilih maksimal 7 hari ke depan.', 'warning');
        return false;
      }
    }

    const durationHours = Number(durationInput.value || 0);
    const startMinutes  = hours * 60 + minutes;
    const endMinutes    = startMinutes + durationHours * 60;

    if (endMinutes > 22 * 60) {
      showToast('Durasi melebihi batas jam 22.00.', 'warning');
      return false;
    }
    return true;
  }

  if (dateInput)     dateInput.addEventListener('change', validateDateTime);
  if (timeInput)     timeInput.addEventListener('change', validateDateTime);
  if (durationInput) durationInput.addEventListener('change', validateDateTime);

  if (categorySelect && bookSelect) {
    fetch('../api/get_categories.php')
      .then(r => r.json())
      .then(data => {
        if (!Array.isArray(data)) return;
        data.forEach(cat => {
          const opt = document.createElement('option');
          opt.value = cat.id;
          opt.textContent = cat.name;
          categorySelect.appendChild(opt);
        });
      })
      .catch(err => console.error('Error load categories:', err));

    categorySelect.addEventListener('change', function() {
      const categoryId = this.value;
      bookSelect.innerHTML = '<option value="">Pilih Buku</option>';
      if (!categoryId) return;
      fetch(`../api/get_books_by_categories.php?category_id=${encodeURIComponent(categoryId)}`)
        .then(r => r.json())
        .then(data => {
          if (!Array.isArray(data)) return;
          data.forEach(book => {
            const opt = document.createElement('option');
            opt.value = book.id;
            opt.textContent = book.title;
            bookSelect.appendChild(opt);
          });
        })
        .catch(err => console.error('Error load books:', err));
    });
  }

  document.addEventListener('click', function(e) {
    const btn = e.target.closest('.room-select-btn');
    if (!btn) return;
    const roomId   = btn.dataset.room;
    const roomName = btn.dataset.roomName;
    showSection('reservation');
    if (typeSelect) {
      typeSelect.value = 'ruangan';
      typeSelect.dispatchEvent(new Event('change', { bubbles: true }));
    }
    const roomSelect = document.getElementById('room');
    if (roomSelect) roomSelect.value = roomId;
    const formContainer = document.querySelector('.reservation-form-container');
    if (formContainer) {
      formContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    showToast(`Ruangan "${roomName}" dipilih. Silakan lengkapi detail reservasi.`, 'info');
  });

  reservationForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    const formData = new FormData(reservationForm);
    const type = formData.get('reservation_type');

    if (!type) {
      showToast('Silakan pilih jenis reservasi terlebih dahulu.', 'warning');
      return;
    }

    if ((type === 'ruangan' || type === 'both') && !validateDateTime()) {
      return;
    }

    console.log('📦 Data yang dikirim:');
    for (let [k, v] of formData.entries()) console.log(k, v);

    let url = '';
    if (type === 'ruangan') {
      url = '../reservation/proses_reservasi_ruangan.php';
    } else if (type === 'buku') {
      url = '../reservation/proses_reservasi_buku.php';
    } else if (type === 'both') {
      url = '../reservation/proses_reservasi_semua.php';
    } else {
      showToast('Jenis reservasi tidak valid.', 'error');
      return;
    }

    try {
      const resp = await fetch(url, { method: 'POST', body: formData });
      if (!resp.ok) throw new Error(`HTTP error! Status: ${resp.status}`);
      const result = await resp.json();
      if (!result.success) {
        showToast(result.error || result.message || 'Terjadi kesalahan saat menyimpan data.', 'error');
        return;
      }

      if (type === 'ruangan') {
        showToast('Reservasi ruangan berhasil disimpan!', 'success');
      } else if (type === 'buku') {
        showToast('Reservasi buku berhasil disimpan!', 'success');
      } else {
        showToast('Reservasi ruangan + buku berhasil disimpan!', 'success');
      }

      reservationForm.reset();
      if (bookSelect) bookSelect.innerHTML = '<option value="">Pilih Buku</option>';
      if (categorySelect) categorySelect.value = '';
      updateMinMaxDate();

      const typeSelectEl = document.getElementById('reservation_type');
      if (typeSelectEl) {
        typeSelectEl.value = '';
        typeSelectEl.dispatchEvent(new Event('change', { bubbles: true }));
      }

    } catch (err) {
      console.error('Fetch error:', err);
      showToast('Gagal menghubungi server. Cek konsol untuk detail.', 'error');
    }
  });
});

// === Carousel ===
function initCarousel() {
  const carouselSlides = document.querySelector('.carousel-slides');
  const slides = document.querySelectorAll('.carousel-slide');
  const prevBtn = document.querySelector('.carousel-btn-prev');
  const nextBtn = document.querySelector('.carousel-btn-next');
  const indicators = document.querySelectorAll('.indicator');

  if (!carouselSlides || slides.length === 0) return;

  let currentSlide = 0;
  const totalSlides = slides.length;

  function updateCarousel() {
    carouselSlides.style.transform = `translateX(-${currentSlide * 100}%)`;
    indicators.forEach((ind, idx) => {
      if (idx === currentSlide) ind.classList.add('active');
      else ind.classList.remove('active');
    });
  }
  function nextSlide() {
    currentSlide = (currentSlide + 1) % totalSlides;
    updateCarousel();
  }
  function prevSlide() {
    currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
    updateCarousel();
  }

  if (nextBtn) {
    const newNextBtn = nextBtn.cloneNode(true);
    nextBtn.parentNode.replaceChild(newNextBtn, nextBtn);
    newNextBtn.addEventListener('click', nextSlide);
  }
  if (prevBtn) {
    const newPrevBtn = prevBtn.cloneNode(true);
    prevBtn.parentNode.replaceChild(newPrevBtn, prevBtn);
    newPrevBtn.addEventListener('click', prevSlide);
  }

  indicators.forEach(indicator => {
    indicator.addEventListener('click', () => {
      const idx = parseInt(indicator.getAttribute('data-slide'), 10);
      if (!Number.isNaN(idx)) {
        currentSlide = idx;
        updateCarousel();
      }
    });
  });
}

function ensureActiveSectionVisible() {
  const activeSection = document.querySelector('.content-section.active');
  if (activeSection) {
    activeSection.style.transform = 'translateX(0)';
    activeSection.style.position = 'relative';
    activeSection.style.zIndex = '1';
  }
}

// === Toggle Form Ruangan / Buku + Required ===
document.addEventListener('DOMContentLoaded', function () {
  const typeSelect  = document.getElementById('reservation_type');
  const formRuangan = document.getElementById('form-ruangan');
  const formBuku    = document.getElementById('form-buku');

  const ruanganFields = ['room', 'people', 'date', 'time', 'duration'];
  const bukuFields    = ['category_id', 'book'];

  function setRequired(fields, required) {
    fields.forEach(id => {
      const el = document.getElementById(id);
      if (!el) return;
      if (required) el.setAttribute('required', 'required');
      else el.removeAttribute('required');
    });
  }

  function handleTypeChange() {
    const type = typeSelect.value;
    if (type === 'ruangan') {
      formRuangan.style.display = 'block';
      formBuku.style.display    = 'none';
      setRequired(ruanganFields, true);
      setRequired(bukuFields, false);
    } else if (type === 'buku') {
      formRuangan.style.display = 'none';
      formBuku.style.display    = 'block';
      setRequired(ruanganFields, false);
      setRequired(bukuFields, true);
    } else if (type === 'both') {
      formRuangan.style.display = 'block';
      formBuku.style.display    = 'block';
      setRequired(ruanganFields, true);
      setRequired(bukuFields, true);
    } else {
      formRuangan.style.display = 'none';
      formBuku.style.display    = 'none';
      setRequired(ruanganFields, false);
      setRequired(bukuFields, false);
    }
  }

  if (typeSelect) {
    typeSelect.addEventListener('change', handleTypeChange);
    handleTypeChange();
  }
});

// === Rooms (dynamic dari DB) ===
async function loadRoomData() {
  try {
    const response = await fetch('../api/get_rooms.php');
    const data = await response.json();
    if (data.success && data.data) {
      renderRooms(data.data);
    }
  } catch (err) {
    console.error('Error loading room data:', err);
  }
}
function renderRooms(rooms) {
  const roomOptions = document.querySelector('.room-options');
  if (!roomOptions) return;
  roomOptions.innerHTML = '';

  const roomSelect = document.getElementById('room');
  if (roomSelect) {
    roomSelect.innerHTML = '';
    const defaultOption = new Option('Pilih Ruangan', '');
    roomSelect.appendChild(defaultOption);
    rooms.forEach(room => {
      const opt = new Option(`${room.name} (${room.capacity} orang)`, room.id);
      roomSelect.add(opt);
    });
  }

  rooms.forEach(room => {
    const roomCard = document.createElement('div');
    roomCard.className = 'room-card';
    roomCard.innerHTML = `
      <div class="room-image">
        <img src="${room.image || 'uploads/default-room.jpg'}" alt="${room.name}">
      </div>
      <div class="room-info">
        <h3>${room.name}</h3>
        <p class="capacity">Kapasitas: ${room.capacity} orang</p>
        <p class="description">${room.description}</p>
        <p class="facilities">${room.facilities}</p>
      </div>`;
    roomOptions.appendChild(roomCard);
  });
}