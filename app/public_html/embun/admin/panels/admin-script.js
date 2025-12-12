// Admin Panel JavaScript - With File Upload Support

// Helper wrapper untuk fetch yang selalu mengirim cookie/session
const apiFetch = (url, options = {}) => {
    return fetch(url, {
        credentials: 'same-origin',
        ...options
    });
};

class AdminPanel {
    constructor() {
        this.currentTab = 'menu';
        this.init();
    }

    init() {
        console.log('AdminPanel init called');
        this.bindEvents();
        this.setupSlugAutoGeneration();
        this.loadData();
    }

    bindEvents() {
        console.log('Binding events...');
        
        // Tab navigation
        document.querySelectorAll('.nav-tab').forEach(tab => {
            tab.addEventListener('click', (e) => {
                e.preventDefault();
                this.switchTab(tab.dataset.tab);
            });
        });

        // Add buttons
        document.getElementById('add-menu-btn').addEventListener('click', () => this.showMenuModal());
        document.getElementById('add-book-btn').addEventListener('click', () => this.showBookModal());
        document.getElementById('add-boardgame-btn').addEventListener('click', () => this.showBoardgameModal());
        document.getElementById('add-menu-category-btn').addEventListener('click', () => this.showMenuCategoryModal());
        document.getElementById('add-book-category-btn').addEventListener('click', () => this.showBookCategoryModal());
        document.getElementById('add-website-content-btn').addEventListener('click', () => this.showWebsiteContentModal());
        document.getElementById('add-room-btn').addEventListener('click', () => this.showRoomModal());

        // Modal close events
        document.querySelectorAll('.close').forEach(btn => {
            btn.addEventListener('click', () => this.closeModals());
        });

        document.getElementById('cancel-menu').addEventListener('click', () => this.closeModals());
        document.getElementById('cancel-book').addEventListener('click', () => this.closeModals());
        document.getElementById('cancel-boardgame').addEventListener('click', () => this.closeModals());
        document.getElementById('cancel-menu-category').addEventListener('click', () => this.closeModals());
        document.getElementById('cancel-book-category').addEventListener('click', () => this.closeModals());
        document.getElementById('cancel-room').addEventListener('click', () => this.closeModals());

        // Form submissions
        document.getElementById('menu-form').addEventListener('submit', (e) => this.saveMenu(e));
        document.getElementById('book-form').addEventListener('submit', (e) => this.saveBook(e));
        document.getElementById('boardgame-form').addEventListener('submit', (e) => this.saveBoardgame(e));
        document.getElementById('menu-category-form').addEventListener('submit', (e) => this.saveMenuCategory(e));
        document.getElementById('book-category-form').addEventListener('submit', (e) => this.saveBookCategory(e));
        document.getElementById('website-content-form').addEventListener('submit', (e) => this.saveWebsiteContent(e));
        document.getElementById('room-form').addEventListener('submit', (e) => this.saveRoom(e));

        // File input change events for preview
        document.getElementById('menu-image').addEventListener('change', (e) => this.previewImage(e, 'menu-image-preview'));
        document.getElementById('book-cover').addEventListener('change', (e) => this.previewImage(e, 'book-cover-preview'));
        document.getElementById('boardgame-image').addEventListener('change', (e) => this.previewImage(e, 'boardgame-image-preview'));
        document.getElementById('room-image').addEventListener('change', (e) => this.previewImage(e, 'room-image-preview'));

        // Website content file events
        this.setupWebsiteContentEvents();

        // Close modal when clicking outside
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) this.closeModals();
            });
        });

        console.log('All events bound successfully');
    }

    // Image preview function
    previewImage(event, previewElementId) {
        const file = event.target.files[0];
        const previewElement = document.getElementById(previewElementId);
        const imgElement = previewElement.querySelector('img');
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imgElement.src = e.target.result;
                previewElement.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            previewElement.style.display = 'none';
        }
    }

    switchTab(tabName) {
        console.log('Switching to tab:', tabName);
        
        // Update active tab
        document.querySelectorAll('.nav-tab').forEach(tab => tab.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
        
        document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');
        document.getElementById(`${tabName}-tab`).classList.add('active');
        
        this.currentTab = tabName;
        
        // Load data for the tab if needed
        if (tabName === 'menu') {
            this.loadMenu();
        } else if (tabName === 'books') {
            this.loadBooks();
        } else if (tabName === 'boardgames') {
            this.loadBoardgames();
        } else if (tabName === 'menu-categories') {
            this.loadMenuCategories();
        } else if (tabName === 'book-categories') {
            this.loadBookCategories();
        } else if (tabName === 'website-content') {
            this.loadWebsiteContent();
        } else if (tabName === 'rooms') {
            this.loadRooms();
        }
    }

    async loadData() {
        console.log('Loading all data...');
        this.showLoading();
        try {
            await this.loadMenu();
            // Books and boardgames will load when their tabs are clicked
        } catch (error) {
            this.showError('Gagal memuat data: ' + error.message);
        } finally {
            this.hideLoading();
        }
    }

    async loadMenu() {
        try {
            console.log('Loading menu data...');
            const response = await apiFetch('../api/admin_api.php?action=get_menu');
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('Menu data received:', data);
            
            if (data.success) {
                this.renderMenuTable(data.menu_items || []);
                this.populateMenuCategories(data.categories || []);
            } else {
                throw new Error(data.error || 'Gagal memuat data menu');
            }
        } catch (error) {
            console.error('Error loading menu:', error);
            this.showError('Gagal memuat data menu: ' + error.message);
            this.renderMenuTable([]);
        }
    }

    async loadBooks() {
        try {
            console.log('Loading books data...');
            const response = await apiFetch('../api/admin_api.php?action=get_book');
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('Books data received:', data);
            
            if (data.success) {
                this.renderBooksTable(data.books || []);
                this.populateBookCategories(data.categories || []);
            } else {
                throw new Error(data.error || 'Gagal memuat data buku');
            }
        } catch (error) {
            console.error('Error loading books:', error);
            this.showError('Gagal memuat data buku: ' + error.message);
            this.renderBooksTable([]);
        }
    }

    async loadBoardgames() {
        try {
            console.log('Loading boardgames data...');
            const response = await apiFetch('../api/admin_api.php?action=get_boardgame');
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('Boardgames data received:', data);
            
            if (data.success) {
                this.renderBoardgamesTable(data.boardgames || []);
            } else {
                throw new Error(data.error || 'Gagal memuat data boardgames');
            }
        } catch (error) {
            console.error('Error loading boardgames:', error);
            this.showError('Gagal memuat data boardgames: ' + error.message);
            this.renderBoardgamesTable([]);
        }
    }

    async loadMenuCategories() {
        try {
            console.log('Loading menu categories...');
            const response = await apiFetch('../api/admin_api.php?action=get_menu');
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('Menu categories data received:', data);
            
            if (data.success) {
                this.renderMenuCategoriesTable(data.categories || []);
            } else {
                throw new Error(data.error || 'Gagal memuat data kategori menu');
            }
        } catch (error) {
            console.error('Error loading menu categories:', error);
            this.showError('Gagal memuat data kategori menu: ' + error.message);
            this.renderMenuCategoriesTable([]);
        }
    }

    async loadBookCategories() {
        try {
            console.log('Loading book categories...');
            const response = await apiFetch('../api/admin_api.php?action=get_book');
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('Book categories data received:', data);
            
            if (data.success) {
                this.renderBookCategoriesTable(data.categories || []);
            } else {
                throw new Error(data.error || 'Gagal memuat data kategori buku');
            }
        } catch (error) {
            console.error('Error loading book categories:', error);
            this.showError('Gagal memuat data kategori buku: ' + error.message);
            this.renderBookCategoriesTable([]);
        }
    }

    renderMenuTable(menuItems) {
        const tbody = document.getElementById('menu-table-body');
        
        if (!menuItems || menuItems.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align: center;">Tidak ada data menu</td></tr>';
            return;
        }

        tbody.innerHTML = '';

        menuItems.forEach(item => {
            const isBest = Number(item.is_best_seller) === 1;

            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${this.escapeHtml(item.name)}</td>
                <td>${this.escapeHtml(item.category_name || 'Uncategorized')}</td>
                <td>Rp ${parseInt(item.price).toLocaleString('id-ID')}</td>
                <td>${
                    isBest
                        ? '<span class="badge badge-success">Ya</span>'
                        : '<span class="badge badge-secondary">Tidak</span>'
                }</td>
                <td class="action-buttons">
                    <button class="btn btn-sm edit-btn" onclick="admin.editMenu(${item.id})">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="btn btn-sm delete-btn" onclick="admin.deleteMenu(${item.id})">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    renderBooksTable(books) {
        const tbody = document.getElementById('books-table-body');
        
        if (!books || books.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align: center;">Tidak ada data buku</td></tr>';
            return;
        }

        tbody.innerHTML = '';

        books.forEach(book => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${this.escapeHtml(book.title)}</td>
                <td>${this.escapeHtml(book.author || '-')}</td>
                <td>${this.escapeHtml(book.category_name || 'Uncategorized')}</td>
                <td class="action-buttons">
                    <button class="btn btn-sm edit-btn" onclick="admin.editBook(${book.id})">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="btn btn-sm delete-btn" onclick="admin.deleteBook(${book.id})">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    renderBoardgamesTable(boardgames) {
        const tbody = document.getElementById('boardgames-table-body');
        
        if (!boardgames || boardgames.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align: center;">Tidak ada data boardgames</td></tr>';
            return;
        }

        tbody.innerHTML = '';

        boardgames.forEach(game => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${this.escapeHtml(game.name)}</td>
                <td>${game.min_players || 1}</td>
                <td>${game.max_players || 4}</td>
                <td>${game.play_time || 30} menit</td>
                <td class="action-buttons">
                    <button class="btn btn-sm edit-btn" onclick="admin.editBoardgame(${game.id})">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="btn btn-sm delete-btn" onclick="admin.deleteBoardgame(${game.id})">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    renderMenuCategoriesTable(categories) {
        const tbody = document.getElementById('menu-categories-table-body');
        
        if (!categories || categories.length === 0) {
            tbody.innerHTML = '<tr><td colspan="3" style="text-align: center;">Tidak ada data kategori</td></tr>';
            return;
        }

        tbody.innerHTML = '';

        categories.forEach(category => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${this.escapeHtml(category.name)}</td>
                <td>${this.escapeHtml(category.slug)}</td>
                <td class="action-buttons">
                    <button class="btn btn-sm edit-btn" onclick="admin.editMenuCategory(${category.id})">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="btn btn-sm delete-btn" onclick="admin.deleteMenuCategory(${category.id})">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    renderBookCategoriesTable(categories) {
        const tbody = document.getElementById('book-categories-table-body');
        
        if (!categories || categories.length === 0) {
            tbody.innerHTML = '<tr><td colspan="3" style="text-align: center;">Tidak ada data kategori</td></tr>';
            return;
        }

        tbody.innerHTML = '';

        categories.forEach(category => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${this.escapeHtml(category.name)}</td>
                <td>${this.escapeHtml(category.slug)}</td>
                <td class="action-buttons">
                    <button class="btn btn-sm edit-btn" onclick="admin.editBookCategory(${category.id})">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="btn btn-sm delete-btn" onclick="admin.deleteBookCategory(${category.id})">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    populateMenuCategories(categories) {
        const select = document.getElementById('menu-category');
        
        if (!categories || categories.length === 0) {
            select.innerHTML = '<option value="">Tidak ada kategori</option>';
            return;
        }

        select.innerHTML = '<option value="">Pilih Kategori</option>';
        
        categories.forEach(cat => {
            const option = document.createElement('option');
            option.value = cat.id;
            option.textContent = cat.name;
            select.appendChild(option);
        });
    }

    populateBookCategories(categories) {
        const select = document.getElementById('book-category');
        
        if (!categories || categories.length === 0) {
            select.innerHTML = '<option value="">Tidak ada kategori</option>';
            return;
        }

        select.innerHTML = '<option value="">Pilih Kategori</option>';
        
        categories.forEach(cat => {
            const option = document.createElement('option');
            option.value = cat.id;
            option.textContent = cat.name;
            select.appendChild(option);
        });
    }

    // Modal Methods
    showMenuModal(menuItem = null) {
        const modal = document.getElementById('menu-modal');
        const title = document.getElementById('menu-modal-title');
        const form = document.getElementById('menu-form');
        
        if (menuItem && menuItem.id) {
            title.textContent = 'Edit Menu';
            console.log('✏️ Editing menu:', menuItem);
            this.populateMenuForm(menuItem);
        } else {
            title.textContent = 'Tambah Menu';
            console.log('➕ Adding new menu');
            form.reset();
            document.getElementById('menu-id').value = '';
            document.getElementById('menu-image').value = '';
            document.getElementById('menu-image-preview').style.display = 'none';
            document.getElementById('menu-best-seller').checked = false; // reset best seller
        }
        
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    showBookModal(book = null) {
        const modal = document.getElementById('book-modal');
        const title = document.getElementById('book-modal-title');
        const form = document.getElementById('book-form');
        
        if (book && book.id) {
            title.textContent = 'Edit Buku';
            console.log('✏️ Editing book:', book);
            this.populateBookForm(book);
        } else {
            title.textContent = 'Tambah Buku';
            console.log('➕ Adding new book');
            form.reset();
            document.getElementById('book-id').value = '';
            document.getElementById('book-cover').value = '';
            document.getElementById('book-cover-preview').style.display = 'none';
        }
        
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    showBoardgameModal(boardgame = null) {
        const modal = document.getElementById('boardgame-modal');
        const title = document.getElementById('boardgame-modal-title');
        const form = document.getElementById('boardgame-form');
        
        if (boardgame && boardgame.id) {
            title.textContent = 'Edit Boardgame';
            console.log('✏️ Editing boardgame:', boardgame);
            this.populateBoardgameForm(boardgame);
        } else {
            title.textContent = 'Tambah Boardgame';
            console.log('➕ Adding new boardgame');
            form.reset();
            document.getElementById('boardgame-id').value = '';
            document.getElementById('boardgame-image').value = '';
            document.getElementById('boardgame-image-preview').style.display = 'none';
        }
        
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    showMenuCategoryModal(category = null) {
        const modal = document.getElementById('menu-category-modal');
        const title = document.getElementById('menu-category-modal-title');
        const form = document.getElementById('menu-category-form');
        
        if (category) {
            title.textContent = 'Edit Kategori Menu';
            this.populateMenuCategoryForm(category);
        } else {
            title.textContent = 'Tambah Kategori Menu';
            form.reset();
            document.getElementById('menu-category-id').value = '';
        }
        
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    showBookCategoryModal(category = null) {
        const modal = document.getElementById('book-category-modal');
        const title = document.getElementById('book-category-modal-title');
        const form = document.getElementById('book-category-form');
        
        if (category) {
            title.textContent = 'Edit Kategori Buku';
            this.populateBookCategoryForm(category);
        } else {
            title.textContent = 'Tambah Kategori Buku';
            form.reset();
            document.getElementById('book-category-id').value = '';
        }
        
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    closeModals() {
        document.querySelectorAll('.modal').forEach(modal => {
            modal.style.display = 'none';
        });
        document.body.style.overflow = 'auto';
        console.log('❌ All modals closed');
    }

    populateMenuForm(menuItem) {
        console.log('📝 Populating menu form with data:', menuItem);
        
        document.getElementById('menu-id').value = menuItem.id;
        document.getElementById('menu-name').value = menuItem.name || '';
        document.getElementById('menu-category').value = menuItem.category_id || '';
        document.getElementById('menu-description').value = menuItem.description || '';
        document.getElementById('menu-price').value = parseInt(menuItem.price) || 0;
        
        // Handle image preview for existing items
        const previewElement = document.getElementById('menu-image-preview');
        const imgElement = previewElement.querySelector('img');
        if (menuItem.image_url && menuItem.image_url !== '') {
            imgElement.src = menuItem.image_url;
            previewElement.style.display = 'block';
            console.log('🖼️ Menu image set:', menuItem.image_url);
        } else {
            previewElement.style.display = 'none';
            console.log('❌ No menu image');
        }
        
        // BEST SELLER FIX: pastikan baca 0/1 dengan benar
        document.getElementById('menu-best-seller').checked =
            Number(menuItem.is_best_seller) === 1;
        
        if (!menuItem.id) {
            document.getElementById('menu-image').value = '';
        }
        
        console.log('✅ Menu form populated successfully');
    }

    populateBookForm(book) {
        console.log('📝 Populating book form with data:', book);
        
        document.getElementById('book-id').value = book.id;
        document.getElementById('book-title').value = book.title || '';
        document.getElementById('book-author').value = book.author || '';
        document.getElementById('book-category').value = book.category_id || '';
        
        const previewElement = document.getElementById('book-cover-preview');
        const imgElement = previewElement.querySelector('img');
        if (book.cover_image && book.cover_image !== '') {
            imgElement.src = book.cover_image;
            previewElement.style.display = 'block';
            console.log('🖼️ Book cover image set:', book.cover_image);
        } else {
            previewElement.style.display = 'none';
            console.log('❌ No book cover image');
        }
    
        if (!book.id) {
            document.getElementById('book-cover').value = '';
        }
    
        console.log('✅ Book form populated successfully');
    }

    populateBoardgameForm(boardgame) {
        console.log('📝 Populating boardgame form with data:', boardgame);
        
        document.getElementById('boardgame-id').value = boardgame.id;
        document.getElementById('boardgame-name').value = boardgame.name || '';
        document.getElementById('boardgame-description').value = boardgame.description || '';
        document.getElementById('boardgame-min-players').value = boardgame.min_players || 1;
        document.getElementById('boardgame-max-players').value = boardgame.max_players || 4;
        document.getElementById('boardgame-play-time').value = boardgame.play_time || 30;
        
        const previewElement = document.getElementById('boardgame-image-preview');
        const imgElement = previewElement.querySelector('img');
        if (boardgame.image_url && boardgame.image_url !== '') {
            imgElement.src = boardgame.image_url;
            previewElement.style.display = 'block';
            console.log('🖼️ Boardgame image set:', boardgame.image_url);
        } else {
            previewElement.style.display = 'none';
            console.log('❌ No boardgame image');
        }
        
        if (!boardgame.id) {
            document.getElementById('boardgame-image').value = '';
        }
        
        console.log('✅ Boardgame form populated successfully');
    }

    populateMenuCategoryForm(category) {
        console.log('📝 Populating menu category form with data:', category);
        
        document.getElementById('menu-category-id').value = category.id;
        document.getElementById('menu-category-name').value = category.name || '';
        document.getElementById('menu-category-slug').value = category.slug || '';
        
        console.log('✅ Menu category form populated successfully');
    }

    populateBookCategoryForm(category) {
        console.log('📝 Populating book category form with data:', category);
        
        document.getElementById('book-category-id').value = category.id;
        document.getElementById('book-category-name').value = category.name || '';
        document.getElementById('book-category-slug').value = category.slug || '';
        
        console.log('✅ Book category form populated successfully');
    }

    async saveMenu(e) {
        e.preventDefault();
        this.showLoading();

        const formData = new FormData();
        formData.append('id', document.getElementById('menu-id').value);
        formData.append('name', document.getElementById('menu-name').value);
        formData.append('category_id', document.getElementById('menu-category').value);
        formData.append('description', document.getElementById('menu-description').value);
        formData.append('price', document.getElementById('menu-price').value);
        formData.append('is_best_seller', document.getElementById('menu-best-seller').checked ? '1' : '0');

        try {
            const imageFile = document.getElementById('menu-image').files[0];
            if (imageFile) {
                console.log('🖼️ Adding menu image...');
                formData.append('menu_image', imageFile);
            }
            const response = await apiFetch('../api/admin_api.php?action=save_menu', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.showSuccess('Menu berhasil disimpan!');
                this.closeModals();
                this.loadMenu();
            } else {
                throw new Error(result.error || 'Gagal menyimpan menu');
            }
        } catch (error) {
            this.showError('Gagal menyimpan menu: ' + error.message);
        } finally {
            this.hideLoading();
        }
    }

    async saveBook(e) {
        e.preventDefault();
        this.showLoading();

        const formData = new FormData();
        formData.append('id', document.getElementById('book-id').value);
        formData.append('title', document.getElementById('book-title').value);
        formData.append('author', document.getElementById('book-author').value);
        formData.append('category_id', document.getElementById('book-category').value);

        try {
            const imageFile = document.getElementById('book-cover').files[0];
            if (imageFile) {
                console.log('🖼️ Adding book cover...');
                formData.append('book_cover', imageFile);
            }

            const response = await apiFetch('../api/admin_api.php?action=save_book', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.showSuccess('Buku berhasil disimpan!');
                this.closeModals();
                this.loadBooks();
            } else {
                throw new Error(result.error || 'Gagal menyimpan buku');
            }
        } catch (error) {
            this.showError('Gagal menyimpan buku: ' + error.message);
        } finally {
            this.hideLoading();
        }
    }

    async saveBoardgame(e) {
        e.preventDefault();
        this.showLoading();

        const formData = new FormData();
        formData.append('id', document.getElementById('boardgame-id').value);
        formData.append('name', document.getElementById('boardgame-name').value);
        formData.append('description', document.getElementById('boardgame-description').value);
        formData.append('min_players', document.getElementById('boardgame-min-players').value);
        formData.append('max_players', document.getElementById('boardgame-max-players').value);
        formData.append('play_time', document.getElementById('boardgame-play-time').value);

        try {
            const imageFile = document.getElementById('boardgame-image').files[0];
            if (imageFile) {
                console.log('🖼️ Adding boardgame image...');
                formData.append('boardgame_image', imageFile);
            }

            const response = await apiFetch('../api/admin_api.php?action=save_boardgame', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.showSuccess('Boardgame berhasil disimpan!');
                this.closeModals();
                this.loadBoardgames();
            } else {
                throw new Error(result.error || 'Gagal menyimpan boardgame');
            }
        } catch (error) {
            this.showError('Gagal menyimpan boardgame: ' + error.message);
        } finally {
            this.hideLoading();
        }
    }

    async saveMenuCategory(e) {
        e.preventDefault();
        this.showLoading();

        const formData = new FormData();
        formData.append('id', document.getElementById('menu-category-id').value);
        formData.append('type', 'menu');
        formData.append('name', document.getElementById('menu-category-name').value);
        formData.append('slug', document.getElementById('menu-category-slug').value);

        try {
            const response = await apiFetch('../api/admin_api.php?action=save_category', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.showSuccess('Kategori menu berhasil disimpan!');
                this.closeModals();
                this.loadMenuCategories();
            } else {
                throw new Error(result.error || 'Gagal menyimpan kategori menu');
            }
        } catch (error) {
            this.showError('Gagal menyimpan kategori menu: ' + error.message);
        } finally {
            this.hideLoading();
        }
    }

    async saveBookCategory(e) {
        e.preventDefault();
        this.showLoading();

        const formData = new FormData();
        formData.append('id', document.getElementById('book-category-id').value);
        formData.append('type', 'book');
        formData.append('name', document.getElementById('book-category-name').value);
        formData.append('slug', document.getElementById('book-category-slug').value);

        try {
            const response = await apiFetch('../api/admin_api.php?action=save_category', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.showSuccess('Kategori buku berhasil disimpan!');
                this.closeModals();
                this.loadBookCategories();
            } else {
                throw new Error(result.error || 'Gagal menyimpan kategori buku');
            }
        } catch (error) {
            this.showError('Gagal menyimpan kategori buku: ' + error.message);
        } finally {
            this.hideLoading();
        }
    }

    async editMenu(id) {
        try {
            console.log('Editing menu item with ID:', id);
            this.showLoading();
            const response = await apiFetch(`../api/admin_api.php?action=get_menu&id=${id}`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            console.log('Edit menu response:', result);

            if (result.success) {
                this.showMenuModal(result.data);
            } else {
                throw new Error(result.error || 'Gagal memuat data menu');
            }
        } catch (error) {
            console.error('Error editing menu:', error);
            this.showError('Gagal memuat data menu: ' + error.message);
        } finally {
            this.hideLoading();
        }
    }

    async editBook(id) {
        try {
            console.log('Editing book with ID:', id);
            this.showLoading();
            const response = await apiFetch(`../api/admin_api.php?action=get_book&id=${id}`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            console.log('Edit book response:', result);

            if (result.success) {
                this.showBookModal(result.data);
            } else {
                throw new Error(result.error || 'Gagal memuat data buku');
            }
        } catch (error) {
            console.error('Error editing book:', error);
            this.showError('Gagal memuat data buku: ' + error.message);
        } finally {
            this.hideLoading();
        }
    }

    async editBoardgame(id) {
        try {
            console.log('Editing boardgame with ID:', id);
            this.showLoading();
            const response = await apiFetch(`../api/admin_api.php?action=get_boardgame&id=${id}`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            console.log('Edit boardgame response:', result);

            if (result.success) {
                this.showBoardgameModal(result.data);
            } else {
                throw new Error(result.error || 'Gagal memuat data boardgame');
            }
        } catch (error) {
            console.error('Error editing boardgame:', error);
            this.showError('Gagal memuat data boardgame: ' + error.message);
        } finally {
            this.hideLoading();
        }
    }

    async editMenuCategory(id) {
        try {
            console.log('✏️ EDIT MENU CATEGORY CALLED with ID:', id);
            this.showLoading();
            
            const response = await apiFetch(`../api/admin_api.php?action=get_category&id=${id}&type=menu`);
            console.log('📡 Edit menu category response status:', response.status);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            console.log('📄 Edit menu category API response:', result);

            if (result.success) {
                console.log('✅ Menu category data received:', result.data);
                this.showMenuCategoryModal(result.data);
            } else {
                console.error('❌ API returned error:', result.error);
                throw new Error(result.error || 'Gagal memuat data kategori menu');
            }
        } catch (error) {
            console.error('💥 Error in editMenuCategory:', error);
            this.showError('Gagal memuat data kategori menu: ' + error.message);
        } finally {
            this.hideLoading();
        }
    }

    async editBookCategory(id) {
        try {
            console.log('✏️ EDIT BOOK CATEGORY CALLED with ID:', id);
            this.showLoading();
            
            const response = await apiFetch(`../api/admin_api.php?action=get_category&id=${id}&type=book`);
            console.log('📡 Edit book category response status:', response.status);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            console.log('📄 Edit book category API response:', result);

            if (result.success) {
                console.log('✅ Book category data received:', result.data);
                this.showBookCategoryModal(result.data);
            } else {
                console.error('❌ API returned error:', result.error);
                throw new Error(result.error || 'Gagal memuat data kategori buku');
            }
        } catch (error) {
            console.error('💥 Error in editBookCategory:', error);
            this.showError('Gagal memuat data kategori buku: ' + error.message);
        } finally {
            this.hideLoading();
        }
    }

    async deleteMenu(id) {
        if (confirm('Apakah Anda yakin ingin menghapus menu ini?')) {
            this.showLoading();
            try {
                const formData = new FormData();
                formData.append('id', id);

                const response = await apiFetch('../api/admin_api.php?action=delete_menu', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const result = await response.json();

                if (result.success) {
                    this.showSuccess('Menu berhasil dihapus!');
                    this.loadMenu();
                } else {
                    throw new Error(result.error || 'Gagal menghapus menu');
                }
            } catch (error) {
                console.error('Error deleting menu:', error);
                this.showError('Gagal menghapus menu: ' + error.message);
            } finally {
                this.hideLoading();
            }
        }
    }

    async deleteBook(id) {
        if (confirm('Apakah Anda yakin ingin menghapus buku ini?')) {
            this.showLoading();
            try {
                const formData = new FormData();
                formData.append('id', id);

                const response = await apiFetch('../api/admin_api.php?action=delete_book', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const result = await response.json();

                if (result.success) {
                    this.showSuccess('Buku berhasil dihapus!');
                    this.loadBooks();
                } else {
                    throw new Error(result.error || 'Gagal menghapus buku');
                }
            } catch (error) {
                console.error('Error deleting book:', error);
                this.showError('Gagal menghapus buku: ' + error.message);
            } finally {
                this.hideLoading();
            }
        }
    }

    async deleteBoardgame(id) {
        if (confirm('Apakah Anda yakin ingin menghapus boardgame ini?')) {
            this.showLoading();
            try {
                const formData = new FormData();
                formData.append('id', id);

                const response = await apiFetch('../api/admin_api.php?action=delete_boardgame', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const result = await response.json();

                if (result.success) {
                    this.showSuccess('Boardgame berhasil dihapus!');
                    this.loadBoardgames();
                } else {
                    throw new Error(result.error || 'Gagal menghapus boardgame');
                }
            } catch (error) {
                console.error('Error deleting boardgame:', error);
                this.showError('Gagal menghapus boardgame: ' + error.message);
            } finally {
                this.hideLoading();
            }
        }
    }

    async deleteMenuCategory(id) {
        if (confirm('Apakah Anda yakin ingin menghapus kategori menu ini? Item menu dalam kategori ini akan kehilangan kategorinya.')) {
            this.showLoading();
            try {
                const formData = new FormData();
                formData.append('id', id);
                formData.append('type', 'menu');

                const response = await apiFetch('../api/admin_api.php?action=delete_category', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const result = await response.json();

                if (result.success) {
                    this.showSuccess('Kategori menu berhasil dihapus!');
                    this.loadMenuCategories();
                } else {
                    throw new Error(result.error || 'Gagal menghapus kategori menu');
                }
            } catch (error) {
                console.error('Error deleting menu category:', error);
                this.showError('Gagal menghapus kategori menu: ' + error.message);
            } finally {
                this.hideLoading();
            }
        }
    }

    async deleteBookCategory(id) {
        if (confirm('Apakah Anda yakin ingin menghapus kategori buku ini? Buku dalam kategori ini akan kehilangan kategorinya.')) {
            this.showLoading();
            try {
                const formData = new FormData();
                formData.append('id', id);
                formData.append('type', 'book');

                const response = await apiFetch('../api/admin_api.php?action=delete_category', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const result = await response.json();

                if (result.success) {
                    this.showSuccess('Kategori buku berhasil dihapus!');
                    this.loadBookCategories();
                } else {
                    throw new Error(result.error || 'Gagal menghapus kategori buku');
                }
            } catch (error) {
                console.error('Error deleting book category:', error);
                this.showError('Gagal menghapus kategori buku: ' + error.message);
            } finally {
                this.hideLoading();
            }
        }
    }

    async loadWebsiteContent() {
        try {
            console.log('Loading website content...');
            const response = await apiFetch('../api/admin_api.php?action=get_website_content');
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('Website content data received:', data);
            
            if (data.success) {
                this.renderWebsiteContentForm(data.content || {});
            } else {
                throw new Error(data.error || 'Gagal memuat data website content');
            }
        } catch (error) {
            console.error('Error loading website content:', error);
            this.showError('Gagal memuat data website content: ' + error.message);
        }
    }

    renderWebsiteContentForm(content) {
        console.log('Rendering website content form with data:', content);
        
        // Hero Section
        if (content.hero_background) {
            const heroBgValue = content.hero_background.value || '';
            document.getElementById('current-hero-bg').textContent = heroBgValue ? 'Image set' : 'No image set';
            document.getElementById('current-hero-bg-modal').textContent = heroBgValue ? 'Image set' : 'No image set';
            
            const preview = document.getElementById('hero-background-preview');
            if (heroBgValue) {
                preview.querySelector('img').src = heroBgValue;
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
            }
        }
        
        if (content.hero_subtitle) {
            document.getElementById('current-hero-subtitle').textContent = content.hero_subtitle.value || '';
            document.getElementById('hero-subtitle').value = content.hero_subtitle.value || '';
        }
        
        // About Section
        if (content.about_title) {
            document.getElementById('current-about-title').textContent = content.about_title.value || '';
            document.getElementById('about-title').value = content.about_title.value || '';
        }
        
        if (content.about_content) {
            document.getElementById('current-about-content').textContent = content.about_content.value || '';
            document.getElementById('about-content').value = content.about_content.value || '';
        }
        
        if (content.about_image) {
            const aboutImgValue = content.about_image.value || '';
            document.getElementById('current-about-image').textContent = aboutImgValue ? 'Image set' : 'No image set';
            document.getElementById('current-about-image-modal').textContent = aboutImgValue ? 'Image set' : 'No image set';
            
            const preview = document.getElementById('about-image-preview');
            if (aboutImgValue) {
                preview.querySelector('img').src = aboutImgValue;
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
            }
        }
    }

    showWebsiteContentModal() {
        const modal = document.getElementById('website-content-modal');
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
        this.loadWebsiteContent();
    }

    async saveWebsiteContent(e) {
        e.preventDefault();
        this.showLoading();

        try {
            let heroBackgroundValue = '';
            let aboutImageValue = '';

            // Hero background file
            const heroBackgroundFile = document.getElementById('hero-background-file').files[0];
            if (heroBackgroundFile) {
                console.log('🖼️ Uploading hero background...');
                const formData = new FormData();
                formData.append('content_key', 'hero_background');
                formData.append('content_file', heroBackgroundFile);
                
                const response = await apiFetch('../api/admin_api.php?action=save_website_content', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                console.log('Hero background upload result:', result);
                
                if (!result.success) {
                    throw new Error(result.error || 'Gagal menyimpan hero background');
                }
                
                heroBackgroundValue = result.message;
            }

            // About image file
            const aboutImageFile = document.getElementById('about-image-file').files[0];
            if (aboutImageFile) {
                console.log('🖼️ Uploading about image...');
                const formData = new FormData();
                formData.append('content_key', 'about_image');
                formData.append('content_file', aboutImageFile);
                
                const response = await apiFetch('../api/admin_api.php?action=save_website_content', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                console.log('About image upload result:', result);
                
                if (!result.success) {
                    throw new Error(result.error || 'Gagal menyimpan about image');
                }
                
                aboutImageValue = result.message;
            }

            // Text content
            const textUpdates = [
                { key: 'hero_subtitle', value: document.getElementById('hero-subtitle').value },
                { key: 'about_title', value: document.getElementById('about-title').value },
                { key: 'about_content', value: document.getElementById('about-content').value }
            ];

            for (const update of textUpdates) {
                const formData = new FormData();
                formData.append('content_key', update.key);
                formData.append('content_value', update.value);
                
                const response = await apiFetch('../api/admin_api.php?action=save_website_content', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                console.log(`Text update for ${update.key}:`, result);
                
                if (!result.success) {
                    throw new Error(result.error || `Gagal menyimpan ${update.key}`);
                }
            }

            this.showSuccess('Website content berhasil disimpan!');
            this.closeModals();
            
            setTimeout(() => {
                this.loadWebsiteContent();
            }, 500);
            
        } catch (error) {
            console.error('Error saving website content:', error);
            this.showError('Gagal menyimpan website content: ' + error.message);
        } finally {
            this.hideLoading();
        }
    }

    setupWebsiteContentEvents() {
        const setupFileEvents = () => {
            const heroBackgroundFile = document.getElementById('hero-background-file');
            const aboutImageFile = document.getElementById('about-image-file');
            
            if (heroBackgroundFile && aboutImageFile) {
                heroBackgroundFile.addEventListener('change', (e) => this.previewImage(e, 'hero-background-preview'));
                aboutImageFile.addEventListener('change', (e) => this.previewImage(e, 'about-image-preview'));
                console.log('✅ Website content file events bound successfully');
            } else {
                console.log('⏳ Website content file inputs not found, retrying...');
                setTimeout(setupFileEvents, 100);
            }
        };
        
        setupFileEvents();
    }

    // Room Management Methods
    async loadRooms() {
        try {
            console.log('Loading rooms data...');
            const response = await apiFetch('../api/admin_api.php?action=get_rooms');
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('Rooms data received:', data);
            
            if (data.success) {
                this.renderRoomsTable(data.data || []);
            } else {
                throw new Error(data.error || 'Failed to load rooms data');
            }
        } catch (error) {
            console.error('Error loading rooms:', error);
            this.showError('Failed to load rooms: ' + error.message);
            this.renderRoomsTable([]);
        }
    }

    renderRoomsTable(rooms) {
        const tbody = document.querySelector('#rooms-table tbody');
        
        if (!rooms || rooms.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" style="text-align: center;">No rooms available</td></tr>';
            return;
        }

        tbody.innerHTML = '';

        rooms.forEach(room => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${this.escapeHtml(room.name)}</td>
                <td>${room.capacity}</td>
                <td>${this.escapeHtml(room.description)}</td>
                <td>${this.escapeHtml(room.facilities || '-')}</td>
                <td>${room.image ? `<img src="${room.image}" alt="Room" style="max-width: 100px;">` : 'No image'}</td>
                <td class="action-buttons">
                    <button class="btn btn-sm edit-btn" onclick="admin.editRoom(${room.id})">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="btn btn-sm delete-btn" onclick="admin.deleteRoom(${room.id})">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    async editRoom(id) {
        try {
            console.log('Editing room with ID:', id);
            this.showLoading();
            const response = await apiFetch(`../api/admin_api.php?action=get_room&id=${id}`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            console.log('Edit room response:', result);

            if (result.success) {
                this.showRoomModal(result.data);
            } else {
                throw new Error(result.error || 'Failed to load room data');
            }
        } catch (error) {
            console.error('Error editing room:', error);
            this.showError('Failed to load room data: ' + error.message);
        } finally {
            this.hideLoading();
        }
    }

    showRoomModal(room = null) {
        const modal = document.getElementById('room-modal');
        const title = modal.querySelector('h2');
        const form = document.getElementById('room-form');
        
        if (room) {
            title.textContent = 'Edit Room';
            this.populateRoomForm(room);
        } else {
            title.textContent = 'Add Room';
            form.reset();
            document.getElementById('room-id').value = '';
            document.getElementById('room-image').value = '';
            document.getElementById('room-image-preview').style.display = 'none';
        }
        
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    populateRoomForm(room) {
        document.getElementById('room-id').value = room.id;
        document.getElementById('room-name').value = room.name || '';
        document.getElementById('room-capacity').value = room.capacity || '';
        document.getElementById('room-description').value = room.description || '';
        document.getElementById('room-facilities').value = room.facilities || '';
        document.getElementById('existing-room-image').value = room.image || '';

        const previewElement = document.getElementById('room-image-preview');
        if (room.image) {
            const img = previewElement.querySelector('img') || document.createElement('img');
            img.src = room.image;
            img.style.maxWidth = '200px';
            if (!previewElement.querySelector('img')) {
                previewElement.appendChild(img);
            }
            previewElement.style.display = 'block';
        } else {
            previewElement.style.display = 'none';
        }
    }

    async saveRoom(e) {
        e.preventDefault();
        this.showLoading();

        const formData = new FormData();
        formData.append('id', document.getElementById('room-id').value);
        formData.append('name', document.getElementById('room-name').value);
        formData.append('capacity', document.getElementById('room-capacity').value);
        formData.append('description', document.getElementById('room-description').value);
        formData.append('facilities', document.getElementById('room-facilities').value);

        const imageFile = document.getElementById('room-image').files[0];
        if (imageFile) {
            formData.append('image', imageFile);
        }

        try {
            const response = await apiFetch('../api/admin_api.php?action=save_room', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.showSuccess('Room saved successfully!');
                this.closeModals();
                this.loadRooms();
            } else {
                throw new Error(result.error || 'Failed to save room');
            }
        } catch (error) {
            this.showError('Failed to save room: ' + error.message);
        } finally {
            this.hideLoading();
        }
    }

    async deleteRoom(id) {
        if (confirm('Are you sure you want to delete this room?')) {
            this.showLoading();
            try {
                const formData = new FormData();
                formData.append('id', id);

                const response = await apiFetch('../api/admin_api.php?action=delete_room', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    this.showSuccess('Room deleted successfully!');
                    this.loadRooms();
                } else {
                    throw new Error(result.error || 'Failed to delete room');
                }
            } catch (error) {
                this.showError('Failed to delete room: ' + error.message);
            } finally {
                this.hideLoading();
            }
        }
    }

    setupSlugAutoGeneration() {
        document.getElementById('menu-category-name').addEventListener('input', (e) => {
            if (!document.getElementById('menu-category-id').value) {
                const slug = this.generateSlug(e.target.value);
                document.getElementById('menu-category-slug').value = slug;
            }
        });

        document.getElementById('book-category-name').addEventListener('input', (e) => {
            if (!document.getElementById('book-category-id').value) {
                const slug = this.generateSlug(e.target.value);
                document.getElementById('book-category-slug').value = slug;
            }
        });
    }

    generateSlug(text) {
        return text
            .toLowerCase()
            .replace(/[^\w ]+/g, '')
            .replace(/ +/g, '-');
    }

    showLoading() {
        document.getElementById('loading').style.display = 'flex';
    }

    hideLoading() {
        document.getElementById('loading').style.display = 'none';
    }

    showSuccess(message) {
        alert('Sukses: ' + message);
    }

    showError(message) {
        alert('Error: ' + message);
    }

    escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

console.log('AdminPanel class defined');
