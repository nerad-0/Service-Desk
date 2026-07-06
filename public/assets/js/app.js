(function () {
    const state = {
        user: null,
        categories: [],
        ticketsPage: 1,
        selectedTicketId: null,
        users: [],
        roles: [],
        adminTab: 'dashboard',
    };

    const labels = {
        status: {
            new: 'Nový',
            open: 'Otevřený',
            in_progress: 'Řeší se',
            waiting_for_user: 'Čeká na uživatele',
            resolved: 'Vyřešený',
            closed: 'Uzavřený',
        },
        priority: {
            low: 'Nízká',
            medium: 'Střední',
            high: 'Vysoká',
            urgent: 'Urgentní',
        },
    };

    const $ = (selector) => document.querySelector(selector);
    const $$ = (selector) => Array.from(document.querySelectorAll(selector));

    document.addEventListener('DOMContentLoaded', init);

    function init() {
        bindEvents();
        loadSession();
    }

    function bindEvents() {
        $('#loginForm').addEventListener('submit', handleLogin);
        $('#logoutButton').addEventListener('click', handleLogout);
        $('#ticketCreateForm').addEventListener('submit', handleCreateTicket);
        $('#toggleTicketForm').addEventListener('click', () => toggleTicketForm(true));
        $('#cancelTicketForm').addEventListener('click', () => toggleTicketForm(false));
        $('#profileForm').addEventListener('submit', handleProfileSave);
        $('#userCreateForm').addEventListener('submit', handleCreateUser);
        $('#categoryCreateForm').addEventListener('submit', handleCreateCategory);

        $$('.nav-button, .brand').forEach((button) => {
            button.addEventListener('click', (event) => {
                event.preventDefault();
                const view = button.dataset.nav;
                if (view) {
                    showView(view);
                }
            });
        });

        ['ticketSearch', 'statusFilter', 'priorityFilter', 'categoryFilter', 'sortFilter'].forEach((id) => {
            $(`#${id}`).addEventListener('input', debounce(() => {
                state.ticketsPage = 1;
                loadTickets();
            }, 250));
        });

        $('#ticketRows').addEventListener('click', (event) => {
            const row = event.target.closest('[data-ticket-id]');
            if (row) {
                loadTicketDetail(row.dataset.ticketId);
            }
        });

        $('#ticketPagination').addEventListener('click', (event) => {
            const button = event.target.closest('[data-page]');
            if (!button) {
                return;
            }
            state.ticketsPage = Number(button.dataset.page);
            loadTickets();
        });

        $('#ticketDetail').addEventListener('submit', handleTicketDetailSubmit);
        $('#ticketDetail').addEventListener('click', handleTicketDetailClick);

        $('#adminTabs').addEventListener('click', (event) => {
            const button = event.target.closest('[data-admin-tab]');
            if (!button) {
                return;
            }
            state.adminTab = button.dataset.adminTab;
            renderAdminTab();
            loadAdminTabData();
        });

        $('#userRows').addEventListener('click', handleUserTableClick);
        $('#categoryRows').addEventListener('click', handleCategoryTableClick);
    }

    async function loadSession() {
        setLoading(true);
        try {
            const data = await api.get('auth/me');
            state.user = data.user;
            api.setCsrfToken(data.csrf_token);
            renderSession();

            if (state.user) {
                await Promise.all([loadCategories(), loadTickets()]);
            }
        } catch (error) {
            if (error.status !== 404) {
                showToast(error.message, true);
            }
            renderLoggedOut();
        } finally {
            setLoading(false);
        }
    }

    async function handleLogin(event) {
        event.preventDefault();
        const form = event.currentTarget;
        const body = objectFromForm(form);
        setLoading(true);
        try {
            const data = await api.post('auth/login', body);
            state.user = data.user;
            api.setCsrfToken(data.csrf_token);
            form.reset();
            showToast('Přihlášení proběhlo úspěšně.');
            renderSession();
            await Promise.all([loadCategories(), loadTickets()]);
        } catch (error) {
            showToast(error.message, true);
        } finally {
            setLoading(false);
        }
    }

    async function handleLogout() {
        setLoading(true);
        try {
            await api.post('auth/logout', {});
            state.user = null;
            api.setCsrfToken('');
            state.selectedTicketId = null;
            showToast('Odhlášení proběhlo úspěšně.');
            renderLoggedOut();
        } catch (error) {
            showToast(error.message, true);
        } finally {
            setLoading(false);
        }
    }

    function renderSession() {
        if (!state.user) {
            renderLoggedOut();
            return;
        }

        $('#authView').hidden = true;
        $('#mainNav').hidden = false;
        $('[data-view]').hidden = true;
        $('#ticketsView').hidden = false;
        $('#logoutButton').hidden = false;
        $('#userChip').hidden = false;
        $('#userChip').textContent = `${state.user.name} · ${state.user.role_label}`;
        $('[data-manager-only]').hidden = !canManageTickets();
        $$('[data-admin-only]').forEach((element) => {
            element.hidden = !isAdmin();
        });
        fillProfileForm();
        setActiveNav('tickets');
    }

    function renderLoggedOut() {
        $('#authView').hidden = false;
        $('#mainNav').hidden = true;
        $$('[data-view]').forEach((view) => {
            view.hidden = true;
        });
        $('#logoutButton').hidden = true;
        $('#userChip').hidden = true;
        $('#userChip').textContent = '';
    }

    function showView(view) {
        if (!state.user) {
            renderLoggedOut();
            return;
        }

        if (view === 'admin' && !canManageTickets()) {
            showToast('Nemáte oprávnění pro administraci.', true);
            return;
        }

        $$('[data-view]').forEach((element) => {
            element.hidden = element.id !== `${view}View`;
        });
        setActiveNav(view);

        if (view === 'admin') {
            renderAdminTab();
            loadAdminTabData();
        }

        if (view === 'profile') {
            fillProfileForm();
        }
    }

    function setActiveNav(view) {
        $$('.nav-button').forEach((button) => {
            button.classList.toggle('active', button.dataset.nav === view);
        });
    }

    async function loadCategories() {
        const data = await api.get('categories');
        state.categories = data.items || [];
        renderCategoryOptions();
    }

    function renderCategoryOptions() {
        const options = ['<option value="">Všechny kategorie</option>']
            .concat(state.categories.map((category) => `<option value="${category.id}">${escapeHtml(category.name)}</option>`));
        $('#categoryFilter').innerHTML = options.join('');

        const createOptions = state.categories.map((category) => `<option value="${category.id}">${escapeHtml(category.name)}</option>`);
        $('#ticketCategorySelect').innerHTML = createOptions.join('');
    }

    async function loadTickets() {
        if (!state.user) {
            return;
        }

        const query = new URLSearchParams({
            page: String(state.ticketsPage),
            per_page: '10',
            sort: $('#sortFilter').value,
            direction: 'desc',
        });

        const search = $('#ticketSearch').value.trim();
        const status = $('#statusFilter').value;
        const priority = $('#priorityFilter').value;
        const categoryId = $('#categoryFilter').value;

        if (search) query.set('search', search);
        if (status) query.set('status', status);
        if (priority) query.set('priority', priority);
        if (categoryId) query.set('category_id', categoryId);

        setLoading(true);
        try {
            const data = await api.get(`tickets?${query.toString()}`);
            renderTickets(data.items || []);
            renderPagination(data.pagination || { page: 1, pages: 1 });
        } catch (error) {
            showToast(error.message, true);
        } finally {
            setLoading(false);
        }
    }

    function renderTickets(tickets) {
        const rows = tickets.map((ticket) => `
            <tr class="click-row" data-ticket-id="${ticket.id}">
                <td>#${ticket.id}</td>
                <td>${escapeHtml(ticket.title)}</td>
                <td>${statusBadge(ticket.status)}</td>
                <td>${priorityBadge(ticket.priority)}</td>
                <td><span class="badge" style="background:${escapeAttr(ticket.category_color)}">${escapeHtml(ticket.category_name)}</span></td>
                <td>${escapeHtml(ticket.author_name || '')}</td>
            </tr>
        `);

        $('#ticketRows').innerHTML = rows.length ? rows.join('') : '<tr><td colspan="6">Žádné požadavky nebyly nalezeny.</td></tr>';
    }

    function renderPagination(pagination) {
        const pages = Math.max(1, Number(pagination.pages || 1));
        const current = Number(pagination.page || 1);
        const buttons = [];

        for (let page = 1; page <= pages; page += 1) {
            buttons.push(`<button type="button" class="${page === current ? 'active' : ''}" data-page="${page}">${page}</button>`);
        }

        $('#ticketPagination').innerHTML = buttons.join('');
    }

    async function handleCreateTicket(event) {
        event.preventDefault();
        const form = event.currentTarget;
        const body = objectFromForm(form);
        body.category_id = Number(body.category_id);
        setLoading(true);
        try {
            const data = await api.post('tickets', body);
            form.reset();
            toggleTicketForm(false);
            showToast('Požadavek byl vytvořen.');
            await loadTickets();
            await loadTicketDetail(data.ticket.id);
        } catch (error) {
            showToast(error.message, true);
        } finally {
            setLoading(false);
        }
    }

    async function loadTicketDetail(id) {
        setLoading(true);
        try {
            if (isAdmin() && state.users.length === 0) {
                const users = await api.get('admin/users');
                state.users = users.items || [];
            }

            const data = await api.get(`tickets/${id}`);
            state.selectedTicketId = Number(id);
            renderTicketDetail(data);
        } catch (error) {
            showToast(error.message, true);
        } finally {
            setLoading(false);
        }
    }

    function renderTicketDetail(data) {
        const ticket = data.ticket;
        const manager = Boolean(data.can_manage);
        const categoryOptions = state.categories.map((category) => (
            `<option value="${category.id}" ${Number(category.id) === Number(ticket.category_id) ? 'selected' : ''}>${escapeHtml(category.name)}</option>`
        )).join('');
        const userOptions = state.users
            .filter((user) => ['TECHNICIAN', 'ADMIN'].includes(user.role_name))
            .map((user) => `<option value="${user.id}" ${Number(user.id) === Number(ticket.assigned_to) ? 'selected' : ''}>${escapeHtml(user.name)}</option>`)
            .join('');

        $('#ticketDetail').innerHTML = `
            <h2>#${ticket.id} ${escapeHtml(ticket.title)}</h2>
            <p class="meta">${escapeHtml(ticket.author_name)} · ${escapeHtml(ticket.created_at)} · ${escapeHtml(ticket.category_name)}</p>
            <form id="ticketUpdateForm" class="stack" data-ticket-id="${ticket.id}">
                <label>
                    Název
                    <input type="text" name="title" minlength="5" maxlength="160" value="${escapeAttr(ticket.title)}" required>
                </label>
                <label>
                    Kategorie
                    <select name="category_id">${categoryOptions}</select>
                </label>
                <label>
                    Priorita
                    <select name="priority">
                        ${priorityOption('low', ticket.priority)}
                        ${priorityOption('medium', ticket.priority)}
                        ${priorityOption('high', ticket.priority)}
                        ${priorityOption('urgent', ticket.priority)}
                    </select>
                </label>
                ${manager ? `
                    <label>
                        Stav
                        <select name="status">
                            ${statusOption('new', ticket.status)}
                            ${statusOption('open', ticket.status)}
                            ${statusOption('in_progress', ticket.status)}
                            ${statusOption('waiting_for_user', ticket.status)}
                            ${statusOption('resolved', ticket.status)}
                            ${statusOption('closed', ticket.status)}
                        </select>
                    </label>
                    ${isAdmin() ? `
                        <label>
                            Přiřazeno
                            <select name="assigned_to">
                                <option value="">Bez přiřazení</option>
                                ${userOptions}
                            </select>
                        </label>
                    ` : ''}
                    <label>
                        Poznámka ke změně stavu
                        <input type="text" name="status_note" maxlength="255">
                    </label>
                ` : ''}
                <label>
                    Popis
                    <textarea name="description" rows="6" minlength="10" maxlength="5000" required>${escapeHtml(ticket.description)}</textarea>
                </label>
                <div class="button-row">
                    <button class="primary-button" type="submit">Uložit změny</button>
                    ${canDeleteTicket(ticket) ? '<button class="ghost-button" type="button" data-delete-ticket>Odstranit</button>' : ''}
                </div>
            </form>

            <hr>
            <h3>Komentáře</h3>
            <div class="comment-list">
                ${(data.comments || []).map(renderComment).join('') || '<div class="empty-state">Zatím zde nejsou žádné komentáře.</div>'}
            </div>
            <form id="commentForm" class="stack" data-ticket-id="${ticket.id}">
                <label>
                    Nový komentář
                    <textarea name="body" rows="3" minlength="2" maxlength="5000" required></textarea>
                </label>
                ${manager ? '<label><input type="checkbox" name="is_internal" value="1"> Interní poznámka</label>' : ''}
                <button class="secondary-button" type="submit">Přidat komentář</button>
            </form>
            ${manager ? renderHistory(data.history || []) : ''}
        `;
    }

    async function handleTicketDetailSubmit(event) {
        const form = event.target;

        if (form.id === 'ticketUpdateForm') {
            event.preventDefault();
            await saveTicket(form);
        }

        if (form.id === 'commentForm') {
            event.preventDefault();
            await addComment(form);
        }
    }

    async function handleTicketDetailClick(event) {
        const button = event.target.closest('[data-delete-ticket]');
        if (!button || !state.selectedTicketId) {
            return;
        }

        if (!confirm('Opravdu odstranit tento požadavek?')) {
            return;
        }

        setLoading(true);
        try {
            await api.delete(`tickets/${state.selectedTicketId}`);
            state.selectedTicketId = null;
            $('#ticketDetail').innerHTML = '<h2>Detail požadavku</h2><div class="empty-state">Požadavek byl odstraněn.</div>';
            showToast('Požadavek byl odstraněn.');
            await loadTickets();
        } catch (error) {
            showToast(error.message, true);
        } finally {
            setLoading(false);
        }
    }

    async function saveTicket(form) {
        const body = objectFromForm(form);
        body.category_id = Number(body.category_id);

        if (body.assigned_to === '') {
            body.assigned_to = null;
        } else if (body.assigned_to !== undefined) {
            body.assigned_to = Number(body.assigned_to);
        }

        setLoading(true);
        try {
            const id = form.dataset.ticketId;
            await api.patch(`tickets/${id}`, body);
            showToast('Požadavek byl uložen.');
            await loadTickets();
            await loadTicketDetail(id);
        } catch (error) {
            showToast(error.message, true);
        } finally {
            setLoading(false);
        }
    }

    async function addComment(form) {
        const body = objectFromForm(form);
        body.is_internal = Boolean(form.querySelector('[name="is_internal"]')?.checked);
        setLoading(true);
        try {
            const id = form.dataset.ticketId;
            await api.post(`tickets/${id}/comments`, body);
            showToast('Komentář byl přidán.');
            await loadTicketDetail(id);
        } catch (error) {
            showToast(error.message, true);
        } finally {
            setLoading(false);
        }
    }

    function toggleTicketForm(open) {
        $('#ticketFormPanel').hidden = !open;
    }

    async function loadAdminTabData() {
        if (state.adminTab === 'dashboard') {
            await loadDashboard();
        }
        if (state.adminTab === 'users' && isAdmin()) {
            await loadUsers();
        }
        if (state.adminTab === 'categories' && isAdmin()) {
            await loadAdminCategories();
        }
        if (state.adminTab === 'audit' && isAdmin()) {
            await loadAuditLog();
        }
    }

    function renderAdminTab() {
        if (!isAdmin() && state.adminTab !== 'dashboard') {
            state.adminTab = 'dashboard';
        }

        $$('[data-admin-panel]').forEach((panel) => {
            panel.hidden = panel.id !== `${state.adminTab}Panel`;
        });
        $$('[data-admin-tab]').forEach((button) => {
            button.classList.toggle('active', button.dataset.adminTab === state.adminTab);
            if (button.dataset.adminOnly !== undefined) {
                button.hidden = !isAdmin();
            }
        });
    }

    async function loadDashboard() {
        setLoading(true);
        try {
            const data = await api.get('admin/dashboard');
            renderDashboard(data);
        } catch (error) {
            showToast(error.message, true);
        } finally {
            setLoading(false);
        }
    }

    function renderDashboard(data) {
        const summary = data.summary || {};
        $('#dashboardStats').innerHTML = [
            summaryMetric('Všechny požadavky', summary.total_tickets || 0),
            summaryMetric('Aktivní práce', summary.active_tickets || 0),
            summaryMetric('Vyřešeno', summary.resolved_tickets || 0),
            summaryMetric('Uzavřeno', summary.closed_tickets || 0),
        ].join('');

        $('#prioritySummary').innerHTML = (data.priority_counts || []).map((item) => `
            <div class="priority-row">
                ${priorityBadge(item.priority)}
                <span>${priorityLabel(item.priority)}</span>
                <strong>${escapeHtml(item.total)}</strong>
            </div>
        `).join('') || '<div class="empty-state">Žádné priority k zobrazení.</div>';

        $('#latestTickets').innerHTML = `<div class="mini-list">${(data.latest_tickets || []).map((ticket) => `
            <div class="ticket-line">
                ${statusBadge(ticket.status)}
                <div>
                    <strong>#${ticket.id} ${escapeHtml(ticket.title)}</strong>
                    <div class="meta">${priorityLabel(ticket.priority)} · ${escapeHtml(ticket.author_name)} · ${escapeHtml(ticket.created_at)}</div>
                </div>
            </div>
        `).join('') || '<div class="empty-state">Žádné požadavky ve frontě.</div>'}</div>`;

        drawStatusChart(data.status_counts || []);
    }

    async function loadUsers() {
        setLoading(true);
        try {
            const [users, roles] = await Promise.all([api.get('admin/users'), api.get('admin/roles')]);
            state.users = users.items || [];
            state.roles = roles.items || [];
            renderUserCreateRoles();
            renderUsers();
        } catch (error) {
            showToast(error.message, true);
        } finally {
            setLoading(false);
        }
    }

    function renderUsers() {
        const roleOptions = (selected) => state.roles.map((role) => (
            `<option value="${role.id}" ${Number(role.id) === Number(selected) ? 'selected' : ''}>${escapeHtml(role.label)}</option>`
        )).join('');

        $('#userRows').innerHTML = state.users.map((user) => `
            <tr data-user-id="${user.id}">
                <td><input type="text" name="name" value="${escapeAttr(user.name)}" maxlength="120"></td>
                <td>${escapeHtml(user.email)}</td>
                <td><select name="role_id">${roleOptions(user.role_id)}</select></td>
                <td><input type="checkbox" name="is_active" ${Number(user.is_active) ? 'checked' : ''}></td>
                <td><button class="ghost-button" type="button" data-save-user>Uložit</button></td>
            </tr>
        `).join('');
    }

    function renderUserCreateRoles() {
        $('#userCreateRoleSelect').innerHTML = state.roles.map((role) => (
            `<option value="${role.id}" ${role.name === 'USER' ? 'selected' : ''}>${escapeHtml(role.label)}</option>`
        )).join('');
    }

    async function handleCreateUser(event) {
        event.preventDefault();
        const form = event.currentTarget;
        const body = objectFromForm(form);
        body.role_id = Number(body.role_id);

        setLoading(true);
        try {
            await api.post('admin/users', body);
            form.reset();
            renderUserCreateRoles();
            showToast('Uživatel byl vytvořen.');
            await loadUsers();
        } catch (error) {
            showToast(error.message, true);
        } finally {
            setLoading(false);
        }
    }

    async function handleUserTableClick(event) {
        const button = event.target.closest('[data-save-user]');
        if (!button) {
            return;
        }

        const row = button.closest('[data-user-id]');
        const body = {
            name: row.querySelector('[name="name"]').value,
            role_id: Number(row.querySelector('[name="role_id"]').value),
            is_active: row.querySelector('[name="is_active"]').checked,
        };

        setLoading(true);
        try {
            await api.patch(`admin/users/${row.dataset.userId}`, body);
            showToast('Uživatel byl uložen.');
            await loadUsers();
        } catch (error) {
            showToast(error.message, true);
        } finally {
            setLoading(false);
        }
    }

    async function loadAdminCategories() {
        setLoading(true);
        try {
            const data = await api.get('admin/categories');
            renderAdminCategories(data.items || []);
        } catch (error) {
            showToast(error.message, true);
        } finally {
            setLoading(false);
        }
    }

    function renderAdminCategories(categories) {
        $('#categoryRows').innerHTML = categories.map((category) => `
            <tr data-category-id="${category.id}">
                <td><input type="text" name="name" value="${escapeAttr(category.name)}" maxlength="100"></td>
                <td><input type="text" name="description" value="${escapeAttr(category.description || '')}" maxlength="255"></td>
                <td><input type="color" name="color" value="${escapeAttr(category.color)}"></td>
                <td><input type="checkbox" name="is_active" ${Number(category.is_active) ? 'checked' : ''}></td>
                <td><button class="ghost-button" type="button" data-save-category>Uložit</button></td>
            </tr>
        `).join('');
    }

    async function handleCreateCategory(event) {
        event.preventDefault();
        setLoading(true);
        try {
            await api.post('admin/categories', objectFromForm(event.currentTarget));
            event.currentTarget.reset();
            showToast('Kategorie byla vytvořena.');
            await Promise.all([loadAdminCategories(), loadCategories()]);
        } catch (error) {
            showToast(error.message, true);
        } finally {
            setLoading(false);
        }
    }

    async function handleCategoryTableClick(event) {
        const button = event.target.closest('[data-save-category]');
        if (!button) {
            return;
        }

        const row = button.closest('[data-category-id]');
        const body = {
            name: row.querySelector('[name="name"]').value,
            description: row.querySelector('[name="description"]').value,
            color: row.querySelector('[name="color"]').value,
            is_active: row.querySelector('[name="is_active"]').checked,
        };

        setLoading(true);
        try {
            await api.patch(`admin/categories/${row.dataset.categoryId}`, body);
            showToast('Kategorie byla uložena.');
            await Promise.all([loadAdminCategories(), loadCategories()]);
        } catch (error) {
            showToast(error.message, true);
        } finally {
            setLoading(false);
        }
    }

    async function loadAuditLog() {
        setLoading(true);
        try {
            const data = await api.get('admin/audit-log');
            $('#auditRows').innerHTML = (data.items || []).map((item) => `
                <div class="audit-item">
                    <strong>${escapeHtml(item.action)}</strong>
                    <div class="meta">${escapeHtml(item.actor_name || 'systém')} · ${escapeHtml(item.entity_type)} #${escapeHtml(item.entity_id || '')} · ${escapeHtml(item.created_at)}</div>
                </div>
            `).join('') || '<div class="empty-state">Auditní log je prázdný.</div>';
        } catch (error) {
            showToast(error.message, true);
        } finally {
            setLoading(false);
        }
    }

    async function handleProfileSave(event) {
        event.preventDefault();
        const form = event.currentTarget;
        const body = objectFromForm(form);

        if (!body.current_password) delete body.current_password;
        if (!body.new_password) delete body.new_password;

        setLoading(true);
        try {
            const data = await api.patch('profile', body);
            state.user = data.user;
            renderSession();
            showView('profile');
            showToast('Profil byl uložen.');
        } catch (error) {
            showToast(error.message, true);
        } finally {
            setLoading(false);
        }
    }

    function fillProfileForm() {
        if (!state.user) {
            return;
        }

        const form = $('#profileForm');
        form.name.value = state.user.name || '';
        form.phone.value = state.user.phone || '';
        form.department.value = state.user.department || '';
        form.current_password.value = '';
        form.new_password.value = '';
    }

    function drawStatusChart(items) {
        const canvas = $('#statusChart');
        const ctx = canvas.getContext('2d');
        const width = canvas.width;
        const height = canvas.height;
        const padding = 42;
        const max = Math.max(1, ...items.map((item) => Number(item.total)));
        const barWidth = (width - padding * 2) / Math.max(1, items.length);

        ctx.clearRect(0, 0, width, height);
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, width, height);
        ctx.strokeStyle = '#d8e0e8';
        ctx.beginPath();
        ctx.moveTo(padding, height - padding);
        ctx.lineTo(width - padding / 2, height - padding);
        ctx.stroke();

        items.forEach((item, index) => {
            const value = Number(item.total);
            const barHeight = ((height - padding * 2) * value) / max;
            const x = padding + index * barWidth + 12;
            const y = height - padding - barHeight;
            const color = statusColor(item.status);

            ctx.fillStyle = color;
            ctx.fillRect(x, y, Math.max(24, barWidth - 24), barHeight);
            ctx.fillStyle = '#17202a';
            ctx.font = '14px Arial';
            ctx.textAlign = 'center';
            ctx.fillText(String(value), x + Math.max(24, barWidth - 24) / 2, y - 8);
            ctx.fillStyle = '#657284';
            ctx.font = '12px Arial';
            ctx.fillText(statusLabel(item.status), x + Math.max(24, barWidth - 24) / 2, height - 14);
        });
    }

    function renderComment(comment) {
        return `
            <article class="comment ${Number(comment.is_internal) ? 'is-internal' : ''}">
                <strong>${escapeHtml(comment.author_name)}</strong>
                <p>${escapeHtml(comment.body)}</p>
                <div class="meta">${Number(comment.is_internal) ? 'Interní poznámka · ' : ''}${escapeHtml(comment.created_at)}</div>
            </article>
        `;
    }

    function renderHistory(history) {
        return `
            <hr>
            <h3>Historie stavů</h3>
            <div class="mini-list">
                ${history.map((item) => `
                    <div class="mini-item">
                        <strong>${statusLabel(item.old_status || 'new')} → ${statusLabel(item.new_status)}</strong>
                        <div class="meta">${escapeHtml(item.changed_by_name)} · ${escapeHtml(item.created_at)} · ${escapeHtml(item.note || '')}</div>
                    </div>
                `).join('') || '<div class="empty-state">Historie je prázdná.</div>'}
            </div>
        `;
    }

    function objectFromForm(form) {
        return Object.fromEntries(new FormData(form).entries());
    }

    function statusOption(value, selected) {
        return `<option value="${value}" ${value === selected ? 'selected' : ''}>${statusLabel(value)}</option>`;
    }

    function priorityOption(value, selected) {
        return `<option value="${value}" ${value === selected ? 'selected' : ''}>${priorityLabel(value)}</option>`;
    }

    function statusBadge(value) {
        return `<span class="badge status-${escapeAttr(value)}">${statusLabel(value)}</span>`;
    }

    function priorityBadge(value) {
        return `<span class="badge priority-${escapeAttr(value)}">${priorityLabel(value)}</span>`;
    }

    function statusLabel(value) {
        return labels.status[value] || value || '';
    }

    function priorityLabel(value) {
        return labels.priority[value] || value || '';
    }

    function statusColor(status) {
        return {
            new: '#2563eb',
            open: '#b45309',
            in_progress: '#0f766e',
            waiting_for_user: '#a16207',
            resolved: '#15803d',
            closed: '#475569',
        }[status] || '#657284';
    }

    function summaryMetric(label, value) {
        return `<div class="summary-metric"><span>${escapeHtml(label)}</span><strong>${escapeHtml(value)}</strong></div>`;
    }

    function canManageTickets() {
        return state.user && ['TECHNICIAN', 'ADMIN'].includes(state.user.role_name);
    }

    function isAdmin() {
        return state.user && state.user.role_name === 'ADMIN';
    }

    function canDeleteTicket(ticket) {
        return isAdmin() || (Number(ticket.author_id) === Number(state.user.id) && ticket.status === 'new');
    }

    function showToast(message, isError) {
        const toast = $('#toast');
        toast.textContent = message;
        toast.classList.toggle('error', Boolean(isError));
        toast.classList.add('show');
        clearTimeout(showToast.timer);
        showToast.timer = setTimeout(() => toast.classList.remove('show'), 3200);
    }

    function setLoading(isLoading) {
        $('#loading').hidden = !isLoading;
    }

    function debounce(callback, delay) {
        let timer = null;
        return (...args) => {
            clearTimeout(timer);
            timer = setTimeout(() => callback(...args), delay);
        };
    }

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, (char) => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;',
        }[char]));
    }

    function escapeAttr(value) {
        return escapeHtml(value);
    }
})();
