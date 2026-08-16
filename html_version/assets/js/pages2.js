/* ============================================================================
   AssetManager HTML Version — Pages Part 2
   Categories, Users, Borrowings, Logs, Trash, Search, Settings, Profile, etc.
   ========================================================================== */
(function (global) {
    'use strict';
    const P = global.Pages;

    // ========================================================================
    // CATEGORIES
    // ========================================================================
    P.categoryIndex = function () {
        if (!Auth.requireAdmin()) return;
        Views.layout(Lang.t('category_list'), 'app');
        const cats = Store.get('categories');
        const assets = Store.get('assets').filter(a => !a.deleted_at);
        cats.forEach(c => { c.asset_count = assets.filter(a => a.category_id == c.id).length; });
        let html = '<div class="card card-primary card-outline"><div class="card-header"><h3 class="card-title"><i class="fas fa-tags mr-1"></i> ' + Lang.t('category_list') + '</h3><div class="card-tools"><button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal-add"><i class="fas fa-plus"></i> ' + Lang.t('add_category') + '</button></div></div><div class="card-body">';
        if (cats.length === 0) html += '<div class="empty-state"><i class="fas fa-tags"></i><p class="mt-3">' + Lang.t('no_data') + '</p></div>';
        else {
            html += '<div class="table-responsive"><table class="table table-hover"><thead><tr><th width="60">#</th><th>' + Lang.t('name') + '</th><th>' + Lang.t('description') + '</th><th class="text-center">' + Lang.t('asset_count') + '</th><th>' + Lang.t('created_at') + '</th><th class="text-center">' + Lang.t('action') + '</th></tr></thead><tbody>';
            cats.forEach(c => {
                html += '<tr><td>' + c.id + '</td><td><strong>' + H.e(c.name) + '</strong></td><td class="text-muted">' + H.e(c.description || '-') + '</td><td class="text-center"><span class="badge badge-' + (c.asset_count > 0 ? 'info' : 'light') + '">' + c.asset_count + '</span></td><td>' + H.tgl(c.created_at) + '</td><td class="text-center"><button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#modal-edit-' + c.id + '"><i class="fas fa-edit"></i></button><form onsubmit="return Pages.deleteCategory(event,' + c.id + ')" class="d-inline"><button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button></form></td></tr>';
            });
            html += '</tbody></table></div>';
        }
        html += '</div></div>';
        // Modal add
        html += '<div class="modal fade" id="modal-add"><div class="modal-dialog"><div class="modal-content"><form onsubmit="return Pages.saveCategory(event,null)"><div class="modal-header"><h5 class="modal-title"><i class="fas fa-plus mr-1"></i> ' + Lang.t('add_category') + '</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div><div class="modal-body"><div class="form-group"><label>' + Lang.t('name') + ' <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" required></div><div class="form-group"><label>' + Lang.t('description') + '</label><textarea name="description" class="form-control" rows="2"></textarea></div></div><div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">' + Lang.t('cancel') + '</button><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> ' + Lang.t('save') + '</button></div></form></div></div></div>';
        // Modal edit
        cats.forEach(c => {
            html += '<div class="modal fade" id="modal-edit-' + c.id + '"><div class="modal-dialog"><div class="modal-content"><form onsubmit="return Pages.saveCategory(event,' + c.id + ')"><div class="modal-header"><h5 class="modal-title"><i class="fas fa-edit mr-1"></i> ' + Lang.t('edit_category') + '</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div><div class="modal-body"><div class="form-group"><label>' + Lang.t('name') + ' <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" required value="' + H.e(c.name) + '"></div><div class="form-group"><label>' + Lang.t('description') + '</label><textarea name="description" class="form-control" rows="2">' + H.e(c.description || '') + '</textarea></div></div><div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">' + Lang.t('cancel') + '</button><button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> ' + Lang.t('save') + '</button></div></form></div></div></div>';
        });
        Views.shell(html);
    };
    P.saveCategory = function (e, id) {
        e.preventDefault();
        const fd = new FormData(e.target);
        const name = fd.get('name'); const description = fd.get('description');
        if (!name) { H.flash('error', Lang.t('category_name_required')); return false; }
        const cats = Store.get('categories');
        if (cats.some(c => c.name.toLowerCase() === name.toLowerCase() && c.id != id)) { H.flash('error', Lang.t('category_name_exists')); return false; }
        if (id) { Store.update('categories', id, { name: name, description: description }); H.audit('categories', 'updated', 'Updated category ' + name); H.flash('success', Lang.t('category_updated')); }
        else { Store.insert('categories', { name: name, description: description }); H.audit('categories', 'created', 'Added category ' + name); H.flash('success', Lang.t('category_added')); }
        Router.navigate('categories');
        return false;
    };
    P.deleteCategory = function (e, id) {
        e.preventDefault();
        const c = Store.get('categories').find(x => x.id == id);
        const used = Store.get('assets').filter(a => a.category_id == id && !a.deleted_at).length;
        if (used > 0) { H.flash('error', Lang.t('category_not_deletable')); Router.navigate('categories'); return false; }
        if (!confirm(Lang.t('delete') + ' ' + (c ? c.name : '') + '?')) return false;
        Store.remove('categories', id);
        H.audit('categories', 'deleted', 'Deleted category ' + (c ? c.name : id));
        H.flash('success', Lang.t('category_deleted'));
        Router.navigate('categories');
        return false;
    };

    // ========================================================================
    // USERS
    // ========================================================================
    P.userIndex = function () {
        if (!Auth.requireAdmin()) return;
        Views.layout(Lang.t('user_list'), 'app');
        const users = Store.get('users');
        let html = '<div class="card card-primary card-outline"><div class="card-header"><h3 class="card-title"><i class="fas fa-users mr-1"></i> ' + Lang.t('user_list') + '</h3><div class="card-tools"><button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal-add"><i class="fas fa-plus"></i> ' + Lang.t('add_user') + '</button></div></div><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>#</th><th>Nama</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th>Dibuat</th><th class="text-center">Aksi</th></tr></thead><tbody>';
        users.forEach(u => {
            html += '<tr><td>' + u.id + '</td><td><strong>' + H.e(u.name) + '</strong></td><td><code>' + H.e(u.username) + '</code></td><td>' + H.e(u.email || '-') + '</td><td>' + H.roleBadge(u.role) + '</td><td>' + (u.is_active == 1 ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-secondary">Nonaktif</span>') + '</td><td>' + H.tgl(u.created_at) + '</td><td class="text-center"><button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#modal-edit-' + u.id + '"><i class="fas fa-edit"></i></button>' + (u.id != Auth.id() ? '<form onsubmit="return Pages.deleteUser(event,' + u.id + ')" class="d-inline"><button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button></form>' : '') + '</td></tr>';
        });
        html += '</tbody></table></div></div></div>';
        // Modal add
        html += '<div class="modal fade" id="modal-add"><div class="modal-dialog"><div class="modal-content"><form onsubmit="return Pages.saveUser(event,null)"><div class="modal-header"><h5 class="modal-title"><i class="fas fa-user-plus mr-1"></i> ' + Lang.t('add_user') + '</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div><div class="modal-body"><div class="form-group"><label>Nama <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" required></div><div class="row"><div class="col-md-6"><div class="form-group"><label>Username <span class="text-danger">*</span></label><input type="text" name="username" class="form-control" required></div></div><div class="col-md-6"><div class="form-group"><label>Email</label><input type="email" name="email" class="form-control"></div></div></div><div class="row"><div class="col-md-6"><div class="form-group"><label>Password <span class="text-danger">*</span></label><input type="password" name="password" class="form-control" required></div></div><div class="col-md-6"><div class="form-group"><label>Role</label><select name="role" class="form-control"><option value="staff">Staff</option><option value="admin">Admin</option></select></div></div></div><div class="form-group"><div class="icheck-primary"><input type="checkbox" name="is_active" value="1" checked id="new_active"><label for="new_active">Aktif</label></div></div></div><div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button></div></form></div></div></div>';
        users.forEach(u => {
            html += '<div class="modal fade" id="modal-edit-' + u.id + '"><div class="modal-dialog"><div class="modal-content"><form onsubmit="return Pages.saveUser(event,' + u.id + ')"><div class="modal-header"><h5 class="modal-title"><i class="fas fa-user-edit mr-1"></i> ' + Lang.t('edit_user') + '</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div><div class="modal-body"><div class="form-group"><label>Nama <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" required value="' + H.e(u.name) + '"></div><div class="form-group"><label>Username <span class="text-danger">*</span></label><input type="text" name="username" class="form-control" required value="' + H.e(u.username) + '"></div><div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" value="' + H.e(u.email || '') + '"></div><div class="row"><div class="col-md-6"><div class="form-group"><label>Password Baru <small class="text-muted">(kosongkan = tidak ubah)</small></label><input type="password" name="password" class="form-control"></div></div><div class="col-md-6"><div class="form-group"><label>Role</label><select name="role" class="form-control"><option value="staff"' + (u.role === 'staff' ? ' selected' : '') + '>Staff</option><option value="admin"' + (u.role === 'admin' ? ' selected' : '') + '>Admin</option></select></div></div><div class="form-group"><div class="icheck-primary"><input type="checkbox" name="is_active" value="1" id="act_' + u.id + '"' + (u.is_active == 1 ? ' checked' : '') + '><label for="act_' + u.id + '">Aktif</label></div></div></div><div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Update</button></div></form></div></div></div>';
        });
        Views.shell(html);
    };
    P.saveUser = function (e, id) {
        e.preventDefault();
        const fd = new FormData(e.target);
        const name = fd.get('name'); const username = fd.get('username'); const email = fd.get('email'); const password = fd.get('password'); const role = fd.get('role'); const is_active = fd.get('is_active') ? 1 : 0;
        if (!name || !username) { H.flash('error', Lang.t('user_name_username_required')); return false; }
        const users = Store.get('users');
        if (users.some(u => u.username.toLowerCase() === username.toLowerCase() && u.id != id)) { H.flash('error', Lang.t('user_username_exists')); return false; }
        if (id) { const patch = { name: name, username: username, email: email, role: role, is_active: is_active }; if (password) patch.password = password; Store.update('users', id, patch); H.audit('users', 'updated', 'Updated user ' + username); H.flash('success', Lang.t('user_updated')); }
        else { if (!password) { H.flash('error', 'Password wajib diisi'); return false; } Store.insert('users', { name: name, username: username, email: email, password: password, role: role, is_active: is_active }); H.audit('users', 'created', 'Added user ' + username); H.flash('success', Lang.t('user_added')); }
        Router.navigate('users');
        return false;
    };
    P.deleteUser = function (e, id) {
        e.preventDefault();
        if (id == Auth.id()) { H.flash('error', Lang.t('user_not_deletable')); return false; }
        const u = Store.get('users').find(x => x.id == id);
        if (!confirm('Hapus user ' + (u ? u.username : '') + '?')) return false;
        Store.remove('users', id);
        H.audit('users', 'deleted', 'Deleted user ' + (u ? u.username : id));
        H.flash('success', Lang.t('user_deleted'));
        Router.navigate('users');
        return false;
    };

    // ========================================================================
    // PROFILE
    // ========================================================================
    P.profile = function () {
        if (!Auth.requireLogin()) return;
        Views.layout(Lang.t('profile'), 'app');
        const u = Store.get('users').find(x => x.id == Auth.id()) || {};
        let html = '<div class="row"><div class="col-md-4"><div class="card card-primary card-outline"><div class="card-body text-center"><div class="d-inline-flex align-items-center justify-content-center bg-info mb-3" style="width:110px;height:110px;border-radius:50%"><span class="text-white" style="font-size:3rem;font-weight:700">' + H.e(String(u.name || '?').charAt(0).toUpperCase()) + '</span></div><h4 class="mb-0">' + H.e(u.name || '') + '</h4><p class="text-muted">@' + H.e(u.username || '') + '</p>' + H.roleBadge(u.role) + '<hr><p class="text-muted small"><i class="fas fa-envelope mr-1"></i> ' + H.e(u.email || '-') + '</p><p class="text-muted small"><i class="fas fa-calendar mr-1"></i> Bergabung: ' + H.tgl(u.created_at) + '</p></div></div></div><div class="col-md-8"><div class="card card-warning card-outline"><div class="card-header"><h3 class="card-title"><i class="fas fa-user-edit mr-1"></i> ' + Lang.t('edit_profile') + '</h3></div><form onsubmit="return Pages.saveProfile(event)"><div class="card-body"><div class="form-group"><label>Nama <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" required value="' + H.e(u.name || '') + '"></div><div class="form-group"><label>Username <small class="text-muted">(tidak bisa diubah)</small></label><input type="text" class="form-control" value="' + H.e(u.username || '') + '" disabled></div><div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" value="' + H.e(u.email || '') + '"></div><hr><div class="form-group"><label>Password Baru</label><input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin mengganti password"><small class="text-muted">Minimal 6 karakter.</small></div></div><div class="card-footer"><button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Simpan Perubahan</button></div></form></div></div></div>';
        Views.shell(html);
    };
    P.saveProfile = function (e) {
        e.preventDefault();
        const fd = new FormData(e.target);
        const name = fd.get('name'); const email = fd.get('email'); const password = fd.get('password');
        const id = Auth.id();
        const patch = { name: name, email: email };
        if (password) patch.password = password;
        Store.update('users', id, patch);
        // refresh session name
        const u = Store.get('users').find(x => x.id == id);
        Store.setSession(u);
        H.audit('auth', 'profile_updated', 'Profile updated');
        H.flash('success', Lang.t('profile_updated'));
        Router.navigate('profile');
        return false;
    };

    // ========================================================================
    // BORROWINGS
    // ========================================================================
    P.borrowings = function (query) {
        if (!Auth.requireLogin()) return;
        Views.layout(Lang.t('borrowing'), 'app');
        const borrowings = Store.get('borrowings').slice().sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
        const assets = Store.get('assets');
        borrowings.forEach(b => { const a = assets.find(x => x.id == b.asset_id); b.asset_code = a ? a.asset_code : ''; b.asset_name = a ? a.name : ''; });
        let html = '<div class="card card-primary card-outline"><div class="card-header"><h3 class="card-title"><i class="fas fa-hand-paper mr-1"></i> ' + Lang.t('borrowing') + ' <small class="text-muted">(' + borrowings.length + ')</small></h3></div><div class="card-body p-0">';
        if (borrowings.length === 0) html += '<div class="empty-state"><i class="fas fa-hand-paper"></i><p class="mt-3">' + Lang.t('no_data') + '</p></div>';
        else {
            html += '<div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>' + Lang.t('asset_code') + '</th><th>' + Lang.t('name') + '</th><th>' + Lang.t('borrower') + '</th><th>' + Lang.t('borrow_date') + '</th><th>' + Lang.t('return_date') + '</th><th>Status</th><th class="text-center">' + Lang.t('action') + '</th></tr></thead><tbody>';
            borrowings.forEach(b => {
                let statusBadge;
                if (b.status === 'borrowed') {
                    statusBadge = (b.expected_return && new Date(b.expected_return.replace(' ', 'T')) < new Date()) ? '<span class="badge badge-danger">Overdue</span>' : '<span class="badge badge-warning">Borrowed</span>';
                } else statusBadge = '<span class="badge badge-success">Returned</span>';
                html += '<tr><td class="asset-code">' + H.e(b.asset_code) + '</td><td>' + H.e(b.asset_name) + '</td><td>' + H.e(b.borrower_name || '-') + '</td><td><small>' + H.tglwaktu(b.borrow_date) + '</small></td><td><small>' + (b.expected_return ? H.tglwaktu(b.expected_return) : '-') + '</small></td><td>' + statusBadge + '</td><td class="text-center">' + (b.status === 'borrowed' ? '<form onsubmit="return Pages.returnAsset(event,' + b.id + ')" class="d-inline"><button class="btn btn-success btn-sm"><i class="fas fa-undo"></i> ' + Lang.t('return_asset') + '</button></form>' : '-') + '</td></tr>';
            });
            html += '</tbody></table></div>';
        }
        html += '</div></div>';
        Views.shell(html);
    };
    P.borrowForm = function (params) {
        const id = params.id;
        if (!Auth.requireLogin()) return;
        Views.layout(Lang.t('borrow'), 'app');
        const a = Store.get('assets').find(x => x.id == id);
        let html = '<div class="card card-primary"><div class="card-header"><h3 class="card-title"><i class="fas fa-hand-paper mr-1"></i> ' + Lang.t('borrow') + ': ' + H.e(a.asset_code) + '</h3></div><form onsubmit="return Pages.saveBorrow(event,' + id + ')"><div class="card-body"><p>' + Lang.t('borrow') + ' <strong>' + H.e(a.name) + '</strong> (' + H.e(a.asset_code) + ')</p><div class="form-group"><label>' + Lang.t('borrower') + ' <span class="text-danger">*</span></label><input type="text" name="borrower_name" class="form-control" required placeholder="' + Lang.t('borrower') + '"></div><div class="form-group"><label>' + Lang.t('return_date') + '</label><input type="datetime-local" name="expected_return" class="form-control"></div><div class="form-group"><label>' + Lang.t('note_optional') + '</label><textarea name="note" class="form-control" rows="2"></textarea></div></div><div class="card-footer"><button type="submit" class="btn btn-warning"><i class="fas fa-hand-paper"></i> ' + Lang.t('borrow') + '</button> <a href="#assets/' + id + '" class="btn btn-default">' + Lang.t('cancel') + '</a></div></form></div>';
        Views.shell(html);
    };
    P.saveBorrow = function (e, id) {
        e.preventDefault();
        const fd = new FormData(e.target);
        const borrower_name = fd.get('borrower_name'); const expected_return = fd.get('expected_return'); const note = fd.get('note');
        const now = new Date();
        const borrow_date = now.toISOString().slice(0, 19).replace('T', ' ');
        Store.insert('borrowings', { asset_id: id, borrower_name: borrower_name, user_id: Auth.id(), borrow_date: borrow_date, expected_return: expected_return ? expected_return.replace('T', ' ') + ':00' : null, actual_return: null, status: 'borrowed', note: note });
        Store.update('assets', id, { status: 'dipinjam' });
        H.assetLog(id, 'dipinjam', note || ('Dipinjam oleh ' + borrower_name));
        H.audit('borrowings', 'borrowed', 'Borrowed asset ' + id);
        H.flash('success', Lang.t('asset_borrowed'));
        Router.navigate('assets/' + id);
        return false;
    };
    P.returnAsset = function (e, id) {
        e.preventDefault();
        const b = Store.get('borrowings').find(x => x.id == id);
        if (!b) return false;
        Store.update('borrowings', id, { status: 'returned', actual_return: new Date().toISOString().slice(0, 19).replace('T', ' ') });
        Store.update('assets', b.asset_id, { status: 'tersedia' });
        H.assetLog(b.asset_id, 'tersedia', 'Asset returned');
        H.audit('borrowings', 'returned', 'Returned asset ' + b.asset_id);
        H.flash('success', Lang.t('asset_returned'));
        Router.navigate('borrowings');
        return false;
    };

    // ========================================================================
    // LOGS (Activity History)
    // ========================================================================
    P.logs = function (query) {
        if (!Auth.requireLogin()) return;
        Views.layout(Lang.t('activity_log'), 'app');
        const page = parseInt(query.page) || 1; const perPage = 20;
        const logs = Store.get('asset_logs').slice().sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
        const assets = Store.get('assets'); const users = Store.get('users');
        logs.forEach(l => { const a = assets.find(x => x.id == l.asset_id) || {}; l.asset_code = a.asset_code || ''; l.asset_name = a.name || ''; const u = users.find(x => x.id == l.user_id) || {}; l.user_name = u.name || (u.username || 'System'); });
        const total = logs.length; const totalPages = Math.max(1, Math.ceil(total / perPage));
        const paged = logs.slice((page - 1) * perPage, page * perPage);
        let html = '<div class="card card-info card-outline"><div class="card-header"><h3 class="card-title"><i class="fas fa-history mr-1"></i> ' + Lang.t('activity_log') + ' <small class="text-muted">(' + total + ' catatan)</small></h3></div><div class="card-body">';
        if (total === 0) html += '<div class="empty-state"><i class="fas fa-clock"></i><p class="mt-3">Belum ada aktivitas tercatat.</p></div>';
        else {
            html += '<div class="table-responsive"><table class="table table-hover"><thead><tr><th>Waktu</th><th>Aset</th><th>Aksi</th><th>Oleh</th><th>Catatan</th></tr></thead><tbody>';
            paged.forEach(l => {
                const cls = l.action === 'dipinjam' ? 'warning' : (l.action === 'rusak' ? 'danger' : (l.action === 'tersedia' ? 'success' : 'secondary'));
                html += '<tr><td><small>' + H.tglwaktu(l.created_at) + '</small></td><td><a href="#assets/' + l.asset_id + '"><span class="asset-code">' + H.e(l.asset_code) + '</span><br><small class="text-muted">' + H.e(l.asset_name) + '</small></a></td><td><span class="badge badge-' + cls + '">' + H.e(l.action) + '</span></td><td><small>' + H.e(l.user_name) + '</small></td><td><small class="text-muted">' + H.e(l.note || '-') + '</small></td></tr>';
            });
            html += '</tbody></table></div>';
        }
        html += '</div>' + H.pagination(page, totalPages, '#logs?', total, perPage) + '</div>';
        Views.shell(html);
    };

    // ========================================================================
    // TRASH
    // ========================================================================
    P.trash = function (query) {
        if (!Auth.requireAdmin()) return;
        Views.layout(Lang.t('trash'), 'app');
        const assets = Store.get('assets').filter(a => a.deleted_at).sort((a, b) => new Date(b.deleted_at) - new Date(a.deleted_at));
        const cats = Store.get('categories');
        assets.forEach(a => { a.category_name = (cats.find(c => c.id == a.category_id) || {}).name || ''; });
        let html = '<div class="card card-warning card-outline"><div class="card-header"><h3 class="card-title"><i class="fas fa-trash-restore mr-1"></i> ' + Lang.t('trash') + ' (' + assets.length + ')</h3><div class="card-tools"><a href="#assets" class="btn btn-default btn-sm"><i class="fas fa-arrow-left"></i> ' + Lang.t('back') + '</a></div></div><div class="card-body p-0">';
        if (assets.length === 0) html += '<div class="empty-state"><i class="fas fa-trash"></i><p class="mt-3">' + Lang.t('no_data') + '</p></div>';
        else {
            html += '<div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>' + Lang.t('asset_code') + '</th><th>' + Lang.t('name') + '</th><th>' + Lang.t('category') + '</th><th>Dihapus</th><th class="text-center">' + Lang.t('action') + '</th></tr></thead><tbody>';
            assets.forEach(a => {
                html += '<tr><td class="asset-code">' + H.e(a.asset_code) + '</td><td>' + H.e(a.name) + '</td><td>' + H.e(a.category_name) + '</td><td><small class="text-muted">' + H.tglwaktu(a.deleted_at) + '</small></td><td class="text-center"><form onsubmit="return Pages.restoreAsset(event,' + a.id + ')" class="d-inline"><button class="btn btn-success btn-sm" title="' + Lang.t('restore') + '"><i class="fas fa-undo"></i> ' + Lang.t('restore') + '</button></form><form onsubmit="return Pages.forceDelete(event,' + a.id + ')" class="d-inline"><button class="btn btn-danger btn-sm"><i class="fas fa-times"></i> ' + Lang.t('permanent_delete') + '</button></form></td></tr>';
            });
            html += '</tbody></table></div>';
        }
        html += '</div></div>';
        Views.shell(html);
    };
    P.restoreAsset = function (e, id) {
        e.preventDefault();
        Store.update('assets', id, { deleted_at: null });
        H.audit('assets', 'restored', 'Restored asset ' + id);
        H.flash('success', Lang.t('restored'));
        Router.navigate('assets/trash');
        return false;
    };
    P.forceDelete = function (e, id) {
        e.preventDefault();
        if (!confirm(Lang.t('permanent_delete_confirm'))) return false;
        Store.remove('assets', id);
        H.audit('assets', 'force_deleted', 'Permanently deleted asset ' + id);
        H.flash('success', Lang.t('permanent_delete'));
        Router.navigate('assets/trash');
        return false;
    };

    // ========================================================================
    // AUDIT
    // ========================================================================
    P.audit = function (query) {
        if (!Auth.requireAdmin()) return;
        Views.layout(Lang.t('audit_trail'), 'app');
        const module = query.module || '';
        const page = parseInt(query.page) || 1; const perPage = 30;
        let logs = Store.get('audit_trail').slice().sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
        const users = Store.get('users');
        logs.forEach(l => { const u = users.find(x => x.id == l.user_id) || {}; l.user_name = u.name || 'System'; });
        if (module) logs = logs.filter(l => l.module === module);
        const modules = [...new Set(Store.get('audit_trail').map(l => l.module))];
        const total = logs.length; const totalPages = Math.max(1, Math.ceil(total / perPage));
        const paged = logs.slice((page - 1) * perPage, page * perPage);
        let html = '<div class="card card-primary card-outline"><div class="card-header"><h3 class="card-title"><i class="fas fa-clipboard-list mr-1"></i> ' + Lang.t('audit_trail') + ' (' + total + ')</h3><div class="card-tools"><form onsubmit="return Pages.filterAudit(event)" class="d-inline"><select name="module" class="form-control form-control-sm d-inline" style="width:140px"><option value="">All ' + Lang.t('module') + '</option>' + modules.map(m => '<option value="' + H.e(m) + '"' + (module === m ? ' selected' : '') + '>' + H.e(m.charAt(0).toUpperCase() + m.slice(1)) + '</option>').join('') + '</select><button type="submit" class="btn btn-sm btn-default ml-1"><i class="fas fa-filter"></i></button></form></div></div><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>' + Lang.t('time') + '</th><th>' + Lang.t('module') + '</th><th>' + Lang.t('action_col') + '</th><th>Description</th><th>User</th><th>IP</th></tr></thead><tbody>';
        paged.forEach(l => { html += '<tr><td><small>' + H.tglwaktu(l.created_at) + '</small></td><td><span class="badge badge-info">' + H.e(l.module) + '</span></td><td><span class="badge badge-secondary">' + H.e(l.action) + '</span></td><td>' + H.e(l.description || '-') + '</td><td><small>' + H.e(l.user_name) + '</small></td><td><small class="text-muted">' + H.e(l.ip || '-') + '</small></td></tr>'; });
        html += '</tbody></table></div></div>' + H.pagination(page, totalPages, '#audit?' + (module ? 'module=' + encodeURIComponent(module) + '&' : ''), total, perPage) + '</div>';
        Views.shell(html);
    };
    P.filterAudit = function (e) {
        e.preventDefault();
        const fd = new FormData(e.target);
        const m = fd.get('module') || '';
        Router.navigate('audit' + (m ? '?module=' + encodeURIComponent(m) : ''));
        return false;
    };

    // ========================================================================
    // NOTIFICATIONS
    // ========================================================================
    P.notifications = function () {
        if (!Auth.requireLogin()) return;
        Views.layout(Lang.t('notifications'), 'app');
        const notifs = Store.get('notifications').slice().sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
        let html = '<div class="card card-primary card-outline"><div class="card-header"><h3 class="card-title"><i class="fas fa-bell mr-1"></i> ' + Lang.t('notifications') + '</h3><div class="card-tools"><form onsubmit="return Pages.markAllRead(event)" class="d-inline"><button class="btn btn-default btn-sm"><i class="fas fa-check-double"></i> Mark all read</button></form></div></div><div class="card-body p-0">';
        if (notifs.length === 0) html += '<div class="empty-state"><i class="far fa-bell"></i><p class="mt-3">' + Lang.t('no_notifications') + '</p></div>';
        else {
            html += '<div class="list-group">';
            notifs.forEach(n => {
                html += '<div class="list-group-item list-group-item-action' + (!n.is_read ? ' list-group-item-light' : '') + '"><div class="d-flex justify-content-between"><div>' + (!n.is_read ? '<span class="badge badge-info">New</span> ' : '') + '<strong>' + H.e(n.title) + '</strong>' + (n.body ? '<br><small class="text-muted">' + H.e(n.body) + '</small>' : '') + '</div><div class="text-right"><small class="text-muted"><i class="far fa-clock"></i> ' + H.tglwaktu(n.created_at) + '</small><br>' + (n.link ? '<a href="' + H.e(n.link) + '" class="btn btn-xs btn-info mt-1">' + Lang.t('view_details') + '</a> ' : '') + (!n.is_read ? '<form onsubmit="return Pages.markRead(event,' + n.id + ')" class="d-inline mt-1"><button class="btn btn-xs btn-default">Mark read</button></form>' : '') + '</div></div></div>';
            });
            html += '</div>';
        }
        html += '</div></div>';
        Views.shell(html);
    };
    P.markRead = function (e, id) {
        e.preventDefault();
        Store.update('notifications', id, { is_read: 1 });
        Router.navigate('notifications');
        return false;
    };
    P.markAllRead = function (e) {
        e.preventDefault();
        const db = Store.load();
        db.notifications = db.notifications.map(n => Object.assign({}, n, { is_read: 1 }));
        Store.save(db);
        Router.navigate('notifications');
        return false;
    };

    // ========================================================================
    // API TOKENS
    // ========================================================================
    P.apiTokens = function () {
        if (!Auth.requireAdmin()) return;
        Views.layout(Lang.t('api_token'), 'app');
        const tokens = Store.get('api_tokens'); const users = Store.get('users');
        tokens.forEach(t => { const u = users.find(x => x.id == t.user_id); t.user_name = u ? u.name : '-'; });
        let html = '<div class="card card-primary card-outline"><div class="card-header"><h3 class="card-title"><i class="fas fa-key mr-1"></i> ' + Lang.t('api_token') + '</h3></div><div class="card-body"><p class="text-muted small">Generate API token for REST API access (simulasi). Use header <code>X-Api-Token</code> or query <code>?token=</code>.</p><form onsubmit="return Pages.generateToken(event)" class="form-inline mb-3"><input type="text" name="name" class="form-control form-control-sm mr-2" placeholder="Token name (optional)"><button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> ' + Lang.t('generate_token') + '</button></form><div class="table-responsive"><table class="table table-hover"><thead><tr><th>Name</th><th>Token</th><th>' + Lang.t('created_at') + '</th><th>Last Used</th><th>User</th><th class="text-center">' + Lang.t('action') + '</th></tr></thead><tbody>';
        tokens.forEach(t => { html += '<tr><td>' + H.e(t.name || '-') + '</td><td><code>' + H.e(String(t.token).slice(0, 16)) + '...</code></td><td><small>' + H.tglwaktu(t.created_at) + '</small></td><td><small>' + (t.last_used_at ? H.tglwaktu(t.last_used_at) : '-') + '</small></td><td><small>' + H.e(t.user_name) + '</small></td><td class="text-center"><form onsubmit="return Pages.deleteToken(event,' + t.id + ')" class="d-inline"><button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button></form></td></tr>'; });
        html += '</tbody></table></div><hr><h6>' + Lang.t('api_docs') + '</h6><pre class="bg-light p-2 rounded"><code>GET #api/assets?token=YOUR_TOKEN\nHeader: X-Api-Token: YOUR_TOKEN</code></pre></div></div>';
        Views.shell(html);
    };
    P.generateToken = function (e) {
        e.preventDefault();
        const fd = new FormData(e.target);
        const name = fd.get('name') || 'Token';
        const token = 'am_' + Math.random().toString(36).slice(2) + Math.random().toString(36).slice(2);
        Store.insert('api_tokens', { name: name, token: token, user_id: Auth.id(), last_used_at: null });
        H.audit('api_tokens', 'generated', 'Generated API token ' + name);
        H.flash('success', Lang.t('token_generated'));
        Router.navigate('api-tokens');
        return false;
    };
    P.deleteToken = function (e, id) {
        e.preventDefault();
        Store.remove('api_tokens', id);
        H.audit('api_tokens', 'deleted', 'Deleted API token ' + id);
        H.flash('success', Lang.t('token_deleted'));
        Router.navigate('api-tokens');
        return false;
    };

    // ========================================================================
    // REST API endpoint (read-only, token optional in demo)
    // ========================================================================
    P.apiAssets = function (query) {
        const token = query.token || '';
        const assets = Store.get('assets').filter(a => !a.deleted_at).map(a => {
            const c = Store.get('categories').find(x => x.id == a.category_id);
            return { id: a.id, asset_code: a.asset_code, name: a.name, category: c ? c.name : null, brand_spec: a.brand_spec, location: a.location, status: a.status, purchase_date: a.purchase_date, price: Number(a.price) };
        });
        document.open(); document.write('<!DOCTYPE html><html><head><meta charset="utf-8"><title>API</title><style>body{font-family:monospace;background:#1a1f2e;color:#c2cbe0;padding:2rem}pre{white-space:pre-wrap}</style></head><body><h2>REST API /api/assets</h2><p>Status: 200 OK ' + (token ? '(authenticated)' : '(demo — no token required)') + '</p><pre>' + H.e(JSON.stringify({ success: true, count: assets.length, data: assets }, null, 2)) + '</pre><p><a href="#api-tokens">Back to API Tokens</a></p></body></html>'); document.close();
    };

    // ========================================================================
    // SETTINGS (Company)
    // ========================================================================
    P.settings = function () {
        if (!Auth.requireAdmin()) return;
        Views.layout(Lang.t('company_settings'), 'app');
        const c = { name: Setting.companyName(), address: Setting.companyAddress(), phone: Setting.companyPhone(), email: Setting.companyEmail() };
        let html = '<div class="row"><div class="col-md-3"><div class="card card-info card-outline"><div class="card-body text-center"><div class="d-inline-flex align-items-center justify-content-center bg-info mb-3" style="width:110px;height:110px;border-radius:50%"><i class="fas fa-building text-white" style="font-size:3rem"></i></div><h4 class="mb-0">' + H.e(c.name) + '</h4><p class="text-muted small">' + Lang.t('company_settings') + '</p></div></div></div><div class="col-md-9"><div class="card card-primary card-outline"><div class="card-header"><h3 class="card-title"><i class="fas fa-building mr-1"></i> ' + Lang.t('company_info') + '</h3></div><form onsubmit="return Pages.saveSettings(event)"><div class="card-body"><div class="form-group"><label>' + Lang.t('company_name') + ' <span class="text-danger">*</span></label><input type="text" name="company_name" class="form-control" required value="' + H.e(c.name) + '" placeholder="AssetManager"><small class="text-muted">' + Lang.t('company_name_hint') + '</small></div><div class="form-group"><label>' + Lang.t('company_address') + '</label><textarea name="company_address" class="form-control" rows="3" placeholder="Street, building, city, postal code">' + H.e(c.address) + '</textarea></div><div class="row"><div class="col-md-6"><div class="form-group"><label><i class="fas fa-phone mr-1"></i> ' + Lang.t('company_phone') + '</label><input type="text" name="company_phone" class="form-control" value="' + H.e(c.phone) + '" placeholder="021-1234567"></div></div><div class="col-md-6"><div class="form-group"><label><i class="fas fa-envelope mr-1"></i> ' + Lang.t('email') + '</label><input type="email" name="company_email" class="form-control" value="' + H.e(c.email) + '" placeholder="info@perusahaan.com"></div></div></div></div><div class="card-footer"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> ' + Lang.t('save') + '</button> <a href="#dashboard" class="btn btn-default"><i class="fas fa-arrow-left"></i> ' + Lang.t('back') + '</a></div></form></div><div class="card card-default card-outline"><div class="card-header"><h3 class="card-title"><i class="fas fa-info-circle mr-1"></i> ' + Lang.t('company_usage') + '</h3></div><div class="card-body"><p class="text-muted small mb-0">' + Lang.t('company_usage_desc') + '</p><ul class="small text-muted mb-0"><li>' + Lang.t('company_usage_sidebar') + '</li><li>' + Lang.t('company_usage_report') + '</li></ul></div></div></div></div>';
        Views.shell(html);
    };
    P.saveSettings = function (e) {
        e.preventDefault();
        const fd = new FormData(e.target);
        Setting.set('company_name', fd.get('company_name'));
        Setting.set('company_address', fd.get('company_address'));
        Setting.set('company_phone', fd.get('company_phone'));
        Setting.set('company_email', fd.get('company_email'));
        H.audit('settings', 'updated', 'Company settings updated');
        H.flash('success', Lang.t('company_settings_saved'));
        Router.navigate('settings');
        return false;
    };

    // ========================================================================
    // SEARCH (global)
    // ========================================================================
    P.search = function (query) {
        if (!Auth.requireLogin()) return;
        Views.layout(Lang.t('search_results'), 'app');
        const q = (query.q || '').toLowerCase();
        const assets = Store.get('assets').filter(a => !a.deleted_at && (a.asset_code + ' ' + a.name + ' ' + (a.brand_spec || '') + ' ' + (a.location || '')).toLowerCase().includes(q));
        const users = Store.get('users').filter(u => (u.name + ' ' + u.username + ' ' + (u.email || '')).toLowerCase().includes(q));
        const cats = Store.get('categories').filter(c => (c.name + ' ' + (c.description || '')).toLowerCase().includes(q));
        const schedules = Store.get('patch_schedules').filter(s => (s.name + ' ' + (s.description || '')).toLowerCase().includes(q));
        let html = '<div class="card card-primary card-outline"><div class="card-header"><h3 class="card-title"><i class="fas fa-search mr-1"></i> ' + Lang.t('search_results') + ': "' + H.e(q) + '"</h3></div><div class="card-body">';
        const count = assets.length + users.length + cats.length + schedules.length;
        if (count === 0) html += '<div class="empty-state"><i class="fas fa-search"></i><p class="mt-3">' + Lang.t('no_results') + '</p></div>';
        else {
            if (assets.length) { html += '<h5><i class="fas fa-box mr-1"></i> ' + Lang.t('assets') + ' (' + assets.length + ')</h5><ul class="list-group mb-3">'; assets.forEach(a => { html += '<li class="list-group-item"><a href="#assets/' + a.id + '"><span class="asset-code">' + H.e(a.asset_code) + '</span> — ' + H.e(a.name) + '</a> ' + H.statusBadge(a.status) + '</li>'; }); html += '</ul>'; }
            if (users.length) { html += '<h5><i class="fas fa-users mr-1"></i> Users (' + users.length + ')</h5><ul class="list-group mb-3">'; users.forEach(u => { html += '<li class="list-group-item"><a href="#users"><i class="fas fa-user mr-1"></i> ' + H.e(u.name) + ' (@' + H.e(u.username) + ')</a> ' + H.roleBadge(u.role) + '</li>'; }); html += '</ul>'; }
            if (cats.length) { html += '<h5><i class="fas fa-tags mr-1"></i> ' + Lang.t('categories') + ' (' + cats.length + ')</h5><ul class="list-group mb-3">'; cats.forEach(c => { html += '<li class="list-group-item"><a href="#categories">' + H.e(c.name) + '</a></li>'; }); html += '</ul>'; }
            if (schedules.length) { html += '<h5><i class="fas fa-shield-alt mr-1"></i> ' + Lang.t('patching') + ' (' + schedules.length + ')</h5><ul class="list-group">'; schedules.forEach(s => { html += '<li class="list-group-item"><a href="#patching/' + s.id + '">' + H.e(s.name) + '</a> Q' + s.quarter + '/' + s.year + '</li>'; }); html += '</ul>'; }
        }
        html += '</div></div>';
        Views.shell(html);
    };

    // ========================================================================
    // DARK MODE / LANGUAGE / SETUP
    // ========================================================================
    P.darkMode = function () {
        const cur = Store.getPref('dark_mode', '0');
        Store.setPref('dark_mode', cur === '1' ? '0' : '1');
        Router.navigate(Router.current().path === '/dark-mode' ? 'dashboard' : Router.current().path);
    };
    P.language = function (params) {
        Lang.set(params.lang || 'en');
        Router.navigate('dashboard');
    };
    P.setup = function () {
        // Reset passwords to defaults
        const users = Store.get('users');
        users.forEach(u => { if (u.username === 'admin') u.password = 'admin123'; if (u.username === 'staff') u.password = 'staff123'; });
        Store.set('users', users);
        H.flash('success', 'Password direset: admin/admin123, staff/staff123');
        Router.navigate('login');
    };

    // ========================================================================
    // IMPORT / EXPORT
    // ========================================================================
    P.exportCsv = function (query) {
        if (!Auth.requireLogin()) return;
        let assets = Store.get('assets').filter(a => !a.deleted_at);
        const cats = Store.get('categories');
        assets.forEach(a => { a.category_name = (cats.find(c => c.id == a.category_id) || {}).name || ''; });
        if (query.status) assets = assets.filter(a => a.status === query.status);
        if (query.category) assets = assets.filter(a => String(a.category_id) === String(query.category));
        const header = 'asset_code,name,category,brand_spec,location,status,purchase_date,price';
        const rows = assets.map(a => [a.asset_code, a.name, a.category_name, a.brand_spec || '', a.location || '', a.status, a.purchase_date || '', a.price].map(v => '"' + String(v).replace(/"/g, '""') + '"').join(','));
        const csv = [header].concat(rows).join('\n');
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8' });
        const a = document.createElement('a'); a.href = URL.createObjectURL(blob); a.download = 'assets.csv'; document.body.appendChild(a); a.click(); a.remove();
        Views.layout(Lang.t('export_csv'), 'app');
        Views.shell('<div class="card card-success card-outline"><div class="card-body"><i class="fas fa-check-circle text-success"></i> ' + assets.length + ' assets exported. <a href="#assets" class="btn btn-default btn-sm">' + Lang.t('back') + '</a></div></div>');
    };
    P.importForm = function () {
        if (!Auth.requireAdmin()) return;
        Views.layout(Lang.t('import_assets'), 'app');
        let html = '<div class="card card-primary card-outline"><div class="card-header"><h3 class="card-title"><i class="fas fa-file-import mr-1"></i> ' + Lang.t('import_assets') + '</h3></div><div class="card-body"><p class="text-muted">CSV format: name, category, brand_spec, location, status, purchase_date, price</p><form onsubmit="return Pages.doImport(event)"><div class="form-group"><label>CSV File</label><input type="file" name="csv" accept=".csv" class="form-control" required></div><button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Import</button> <a href="#assets" class="btn btn-default">' + Lang.t('back') + '</a></form></div></div>';
        Views.shell(html);
    };
    P.doImport = function (e) {
        e.preventDefault();
        const file = e.target.csv.files[0];
        if (!file) return false;
        const reader = new FileReader();
        reader.onload = ev => {
            const text = ev.target.result;
            const lines = text.trim().split(/\r\n|\r|\n/);
            let count = 0;
            const cats = Store.get('categories');
            for (let i = 1; i < lines.length; i++) {
                const cols = lines[i].match(/(".*?"|[^",\s]+)(?=\s*,|\s*$)/g) || [];
                if (cols.length < 7) continue;
                const clean = cols.map(c => c.replace(/^"|"$/g, ''));
                const [name, category, brand_spec, location, status, purchase_date, price] = clean;
                if (!name) continue;
                let cat = cats.find(c => c.name.toLowerCase() === String(category).toLowerCase());
                if (!cat) { cat = Store.insert('categories', { name: category || 'Umum', description: '' }); }
                Store.insert('assets', { asset_code: H.generateAssetCode(), name: name, category_id: cat.id, brand_spec: brand_spec, location: location, status: status || 'tersedia', purchase_date: purchase_date, price: Number(price) || 0, photo: '', deleted_at: null });
                count++;
            }
            H.audit('assets', 'imported', 'Imported ' + count + ' assets via CSV');
            H.flash('success', Lang.t('import_success', { count: count }));
            Router.navigate('assets');
        };
        reader.readAsText(file);
        return false;
    };

    global.Pages = P;
})(window);
