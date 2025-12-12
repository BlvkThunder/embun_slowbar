// Admin Panel JavaScript
class AdminPanel {
    constructor() {
        this.currentTab = 'menu';
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadData();
    }

    bindEvents() {
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

        // Modal close events
        document.querySelectorAll('.close').forEach(btn => {
            btn.addEventListener('click', () => this.closeModals());
        });

        document.getElementById('cancel-menu').addEventListener('click', () => this.closeModals());
        document.getElementById('cancel-book').addEventListener('click', () => this.closeModals());
        document.getElementById('cancel-boardgame').addEventListener('click', () => this.closeModals());

        // Form submissions
        document.getElementById('menu-form').addEventListener('submit', (e) => this.saveMenu(e));
        document.getElementById('book-form').addEventListener('submit', (e) => this.saveBook(e));
        document.getElementById('boardgame-form').addEventListener('submit', (e) => this.saveBoardgame(e));

        // Close modal when clicking outside
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) this.closeModals();
            });
        });
    }

    switchTab(tabName) {
        // Update active tab
        document.querySelectorAll('.nav-tab').forEach(tab => tab.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
        
        document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');
        document.getElementById(`${tabName}-tab`).classList.add('active');
        
        this.currentTab = tabName;
    }

    async loadData() {
        this.showLoading();
        try {
            await Promise.all([
                this.loadMenu(),
                this.loadBooks(),
                this.loadBoardgames()
            ]);
        } catch (error) {
            this.showError('Gagal memuat data: ' + error.message);
        } finally {
            this.hideLoading();
        }
    }

    async loadMenu() {
        try {
            const response = await fetch('../api/admin_api.php?action=get_menu');
            const data = await response.json();
            
            if (data.success) {
                this.renderMenuTable(data.menu_items);
                this.populateMenuCategories(data.categories);
            } else {
                throw new Error(data.error || 'Gagal memuat data menu');
            }
        } catch (error) {
            console.error('Error loading menu:', error);
            this.showError('Gagal memuat data menu');
        }
    }

    async loadBooks() {
        try {
            const response = await fetch('../api/admin_api.php?action=get_book');
            const data = await response.json();
            
            if (data.success) {
                this.renderBooksTable(data.books);
                this.populateBookCategories(data.categories);
            } else {
                throw new Error(data.error || 'Gagal memuat data buku');
            }
        } catch (error) {
            console.error('Error loading books:', error);
            this.showError('Gagal memuat data buku');
        }
    }

    async loadBoardgames() {
        try {
            const response = await fetch('../api/admin_api.php?action=get_boardgame');
            const data = await response.json();
            
            if (data.success) {
                this.renderBoardgamesTable(data.boardgames);
            } else {
                throw new Error(data.error || 'Gagal memuat data boardgames');
            }
        } catch (error) {
            console.error('Error loading boardgames:', error);
            this.showError('Gagal memuat data boardgames');
        }
    }

    renderMenuTable(menuItems) {
        const tbody = document.getElementById('menu-table-body');
        tbody.innerHTML = '';

        menuItems.forEach(item => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${this.escapeHtml(item.name)}</td>
                <td>${this.escapeHtml(item.category_name)}</td>
                <td>Rp ${parseInt(item.price).toLocaleString('id-ID')}</td>
                <td>${item.is_best_seller ? '<span class="badge badge-success">Ya</span>' : '<span class="badge badge-secondary">Tidak</span>'}</td>
                <td>${item.display_order}</td>
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
        tbody.innerHTML = '';

        books.forEach(book => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${this.escapeHtml(book.title)}</td>
                <td>${this.escapeHtml(book.author || '-')}</td>
                <td>${this.escapeHtml(book.category_name)}</td>
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
        tbody.innerHTML = '';

        boardgames.forEach(game => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${this.escapeHtml(game.name)}</td>
                <td>${game.min_players}</td>
                <td>${game.max_players}</td>
                <td>${game.play_time} menit</td>
                <td>${game.display_order}</td>
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

    populateMenuCategories(categories) {
        const select = document.getElementById('menu-category');
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
        
        if (menuItem) {
            title.textContent = 'Edit Menu';
            this.populateMenuForm(menuItem);
        } else {
            title.textContent = 'Tambah Menu';
            form.reset();
            document.getElementById('menu-id').value = '';
        }
        
        modal.style.display = 'block';
    }

    showBookModal(book = null) {
        const modal = document.getElementById('book-modal');
        const title = document.getElementById('book-modal-title');
        const form = document.getElementById('book-form');
        
        if (book) {
            title.textContent = 'Edit Buku';
            this.populateBookForm(book);
        } else {
            title.textContent = 'Tambah Buku';
            form.reset();
            document.getElementById('book-id').value = '';
        }
        
        modal.style.display = 'block';
    }

    showBoardgameModal(boardgame = null) {
        const modal = document.getElementById('boardgame-modal');
        const title = document.getElementById('boardgame-modal-title');
        const form = document.getElementById('boardgame-form');
        
        if (boardgame) {
            title.textContent = 'Edit Boardgame';
            this.populateBoardgameForm(boardgame);
        } else {
            title.textContent = 'Tambah Boardgame';
            form.reset();
            document.getElementById('boardgame-id').value = '';
        }
        
        modal.style.display = 'block';
    }

    closeModals() {
        document.querySelectorAll('.modal').forEach(modal => {
            modal.style.display = 'none';
        });
    }

    // Form Population Methods
    populateMenuForm(menuItem) {
        document.getElementById('menu-id').value = menuItem.id;
        document.getElementById('menu-name').value = menuItem.name;
        document.getElementById('menu-category').value = menuItem.category_id;
        document.getElementById('menu-description').value = menuItem.description || '';
        document.getElementById('menu-price').value = parseInt(menuItem.price);
        document.getElementById('menu-order').value = menuItem.display_order;
        document.getElementById('menu-image').value = menuItem.image_url || '';
        document.getElementById('menu-best-seller').checked = Boolean(menuItem.is_best_seller);
    }

    populateBookForm(book) {
        document.getElementById('book-id').value = book.id;
        document.getElementById('book-title').value = book.title;
        document.getElementById('book-author').value = book.author || '';
        document.getElementById('book-category').value = book.category_id;
        document.getElementById('book-cover').value = book.cover_image || '';
    }

    populateBoardgameForm(boardgame) {
        document.getElementById('boardgame-id').value = boardgame.id;
        document.getElementById('boardgame-name').value = boardgame.name;
        document.getElementById('boardgame-description').value = boardgame.description || '';
        document.getElementById('boardgame-min-players').value = boardgame.min_players || 1;
        document.getElementById('boardgame-max-players').value = boardgame.max_players || 4;
        document.getElementById('boardgame-play-time').value = boardgame.play_time || 30;
        document.getElementById('boardgame-order').value = boardgame.display_order || 0;
        document.getElementById('boardgame-image').value = boardgame.image_url || '';
    }

    // Save Methods
    async saveMenu(e) {
        e.preventDefault();
        this.showLoading();

        const formData = new FormData();
        formData.append('id', document.getElementById('menu-id').value);
        formData.append('name', document.getElementById('menu-name').value);
        formData.append('category_id', document.getElementById('menu-category').value);
        formData.append('description', document.getElementById('menu-description').value);
        formData.append('price', document.getElementById('menu-price').value);
        formData.append('display_order', document.getElementById('menu-order').value);
        formData.append('image_url', document.getElementById('menu-image').value);
        formData.append('is_best_seller', document.getElementById('menu-best-seller').checked ? '1' : '0');

        try {
            const response = await fetch('../api/admin_api.php?action=save_menu', {
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
        formData.append('cover_image', document.getElementById('book-cover').value);

        try {
            const response = await fetch('../api/admin_api.php?action=save_book', {
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
        formData.append('display_order', document.getElementById('boardgame-order').value);
        formData.append('image_url', document.getElementById('boardgame-image').value);

        try {
            const response = await fetch('../api/admin_api.php?action=save_boardgame', {
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

    // Edit Methods
    async editMenu(id) {
        try {
            const response = await fetch(`../api/admin_api.php?action=get_menu&id=${id}`);
            const result = await response.json();

            if (result.success) {
                this.showMenuModal(result.data);
            } else {
                throw new Error(result.error || 'Gagal memuat data menu');
            }
        } catch (error) {
            this.showError('Gagal memuat data menu: ' + error.message);
        }
    }

    async editBook(id) {
        try {
            const response = await fetch(`../api/admin_api.php?action=get_book&id=${id}`);
            const result = await response.json();

            if (result.success) {
                this.showBookModal(result.data);
            } else {
                throw new Error(result.error || 'Gagal memuat data buku');
            }
        } catch (error) {
            this.showError('Gagal memuat data buku: ' + error.message);
        }
    }

    async editBoardgame(id) {
        try {
            const response = await fetch(`../api/admin_api.php?action=get_boardgame&id=${id}`);
            const result = await response.json();

            if (result.success) {
                this.showBoardgameModal(result.data);
            } else {
                throw new Error(result.error || 'Gagal memuat data boardgame');
            }
        } catch (error) {
            this.showError('Gagal memuat data boardgame: ' + error.message);
        }
    }

    // Delete Methods
    async deleteMenu(id) {
        if (confirm('Apakah Anda yakin ingin menghapus menu ini?')) {
            this.showLoading();
            try {
                const response = await fetch('../api/admin_api.php?action=delete_menu', {
                    method: 'POST',
                    body: new URLSearchParams({ id: id })
                });

                const result = await response.json();

                if (result.success) {
                    this.showSuccess('Menu berhasil dihapus!');
                    this.loadMenu();
                } else {
                    throw new Error(result.error || 'Gagal menghapus menu');
                }
            } catch (error) {
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
                const response = await fetch('../api/admin_api.php?action=delete_book', {
                    method: 'POST',
                    body: new URLSearchParams({ id: id })
                });

                const result = await response.json();

                if (result.success) {
                    this.showSuccess('Buku berhasil dihapus!');
                    this.loadBooks();
                } else {
                    throw new Error(result.error || 'Gagal menghapus buku');
                }
            } catch (error) {
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
                const response = await fetch('../api/admin_api.php?action=delete_boardgame', {
                    method: 'POST',
                    body: new URLSearchParams({ id: id })
                });

                const result = await response.json();

                if (result.success) {
                    this.showSuccess('Boardgame berhasil dihapus!');
                    this.loadBoardgames();
                } else {
                    throw new Error(result.error || 'Gagal menghapus boardgame');
                }
            } catch (error) {
                this.showError('Gagal menghapus boardgame: ' + error.message);
            } finally {
                this.hideLoading();
            }
        }
    }

    // Utility Methods
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
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}