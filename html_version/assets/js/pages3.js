/* ============================================================================
   AssetManager HTML Version — Pages Part 3
   Patching (schedules/show/checklist/computers/form) + Reports (tabs + print).
   ========================================================================== */
(function (global) {
    'use strict';
    const P = global.Pages;

    // Helper: progress per checklist
    function checklistProgress(checklistId) {
        const items = Store.get('patch_checklist_items').filter(i => i.checklist_id == checklistId);
        const total = items.length;
        const done = items.filter(i => i.is_checked == 1).length;
        return { done: done, total: total };
    }
    function refreshScheduleStatus(scheduleId) {
        const checklists = Store.get('patch_checklists').filter(c => c.schedule_id == scheduleId);
        if (checklists.length === 0) return;
        const allDone = checklists.every(c => c.status === 'completed' || c.status === 'skipped');
        const anyProgress = checklists.some(c => c.status === 'in_progress' || c.status === 'completed');
        let status = 'draft';
        if (allDone) status = 'completed';
        else if (anyProgress) status = 'ongoing';
        Store.update('patch_schedules', scheduleId, { status: status });
    }
    function refreshChecklistStatus(checklistId) {
        const items = Store.get('patch_checklist_items').filter(i => i.checklist_id == checklistId);
        const cl = Store.get('patch_checklists').find(c => c.id == checklistId);
        if (!cl) return;
        const total = items.length;
        const done = items.filter(i => i.is_checked == 1).length;
        let status = 'pending';
        if (done === total && total > 0) status = 'completed';
        else if (done > 0) status = 'in_progress';
        if (cl.status === 'skipped') return; // keep skipped unless reset
        Store.update('patch_checklists', checklistId, { status: status, patched_by: done > 0 ? (cl.patched_by || Auth.id()) : cl.patched_by, patched_at: status === 'completed' ? new Date().toISOString() : cl.patched_at });
        // schedule refresh
        refreshScheduleStatus(cl.schedule_id);
    }

    // ========================================================================
    // PATCHING — INDEX (list of schedules)
    // ========================================================================
    P.patching = function () {
        if (!Auth.requireLogin()) return;
        Views.layout(Lang.t('patch_schedule'), 'app');
        const schedules = Store.get('patch_schedules').slice().sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
        const checklists = Store.get('patch_checklists');
        schedules.forEach(s => {
            const cls = checklists.filter(c => c.schedule_id == s.id);
            s.total_aset = cls.length;
            s.done_aset = cls.filter(c => c.status === 'completed').length;
            s.progress_aset = cls.filter(c => c.status === 'in_progress').length;
            s.pending_aset = cls.filter(c => c.status === 'pending').length;
            s.skipped_aset = cls.filter(c => c.status === 'skipped').length;
        });
        let html = '<div class="card card-primary card-outline"><div class="card-header"><h3 class="card-title"><i class="fas fa-shield-alt mr-1"></i> ' + Lang.t('patch_schedule') + ' (Kuartal / per 3 ' + Lang.t('data') + ')</h3>' + (Auth.isAdmin() ? '<div class="card-tools"><a href="#patching/create" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> ' + Lang.t('create_schedule') + '</a></div>' : '') + '</div><div class="card-body"><p class="text-muted small"><i class="fas fa-info-circle"></i> Patching & maintenance schedules for IT assets done every 3 months (quarterly).</p>';
        if (schedules.length === 0) html += '<div class="empty-state"><i class="fas fa-shield-alt"></i><p class="mt-3">' + Lang.t('no_schedules') + (Auth.isAdmin() ? ' ' + Lang.t('create_schedule') + '.' : '') + '</p></div>';
        else {
            html += '<div class="table-responsive"><table class="table table-hover"><thead><tr><th>' + Lang.t('schedule_name') + '</th><th>Periode</th><th>Rentang</th><th>Status</th><th class="text-center">Progress</th><th class="text-center">Aksi</th></tr></thead><tbody>';
            schedules.forEach(s => {
                const pct = s.total_aset > 0 ? Math.round((s.done_aset / s.total_aset) * 100) : 0;
                html += '<tr><td><strong>' + H.e(s.name) + '</strong>' + (s.description ? '<br><small class="text-muted">' + H.e(s.description) + '</small>' : '') + '</td><td>Q' + s.quarter + ' / ' + s.year + '</td><td><small>' + H.tgl(s.start_date) + ' <br><i class="fas fa-arrow-right text-muted"></i> ' + H.tgl(s.due_date) + '</small></td><td>' + H.patchStatusBadge(s.status) + '</td><td class="text-center"><div style="min-width:110px"><div class="progress progress-sm mb-1"><div class="progress-bar bg-' + (pct >= 100 ? 'success' : (pct > 0 ? 'warning' : 'secondary')) + '" style="width:' + pct + '%"></div></div><small>' + s.done_aset + '/' + s.total_aset + ' aset (' + pct + '%)</small></div></td><td class="text-center"><a href="#patching/' + s.id + '" class="btn btn-info btn-sm" title="Detail"><i class="fas fa-eye"></i></a>' + (Auth.isAdmin() ? ' <a href="#patching/' + s.id + '/edit" class="btn btn-warning btn-sm" title="Edit"><i class="fas fa-edit"></i></a>' : '') + '</td></tr>';
            });
            html += '</tbody></table></div>';
        }
        html += '</div></div>';
        Views.shell(html);
    };

    // ========================================================================
    // PATCHING — FORM (create/edit)
    // ========================================================================
    P.patchForm = function (params) {
        const id = (typeof params === 'object' && params !== null) ? (params.id || null) : (params || null);
        if (!Auth.requireAdmin()) return;
        const isEdit = !!id;
        let s = { name: '', quarter: 3, year: new Date().getFullYear(), start_date: '', due_date: '', status: 'draft', description: '' };
        if (isEdit) s = Store.get('patch_schedules').find(x => x.id == id) || s;
        Views.layout(isEdit ? 'Edit Jadwal Patching' : 'Buat Jadwal Patching', 'app');
        const quarterOpts = [1, 2, 3, 4].map(q => ({ quarter: q, year: new Date().getFullYear() }));
        let html = '<div class="card card-primary"><div class="card-header"><h3 class="card-title"><i class="fas fa-' + (isEdit ? 'edit' : 'plus') + ' mr-1"></i> ' + (isEdit ? 'Edit Jadwal Patching' : 'Buat Jadwal Patching Baru') + '</h3></div><form onsubmit="return Pages.saveSchedule(event,' + (isEdit ? id : 'null') + ')"><div class="card-body"><div class="form-group"><label>Nama Jadwal <span class="text-danger">*</span></label><input type="text" name="name" id="sched-name" class="form-control" required value="' + H.e(s.name) + '" placeholder="Mis: Patching Q3 2026"><small class="text-muted">Akan terisi otomatis dari kuartal & tahun bila dikosongkan.</small></div><div class="row"><div class="col-md-3"><div class="form-group"><label>Kuartal <span class="text-danger">*</span></label><select name="quarter" id="sched-quarter" class="form-control" required>' + quarterOpts.map(o => '<option value="' + o.quarter + '"' + (s.quarter == o.quarter && s.year == o.year ? ' selected' : '') + '>Q' + o.quarter + ' (' + o.year + ')</option>').join('') + '</select></div></div><div class="col-md-3"><div class="form-group"><label>Tahun <span class="text-danger">*</span></label><input type="number" name="year" id="sched-year" class="form-control" required min="2020" max="2099" value="' + s.year + '"></div></div><div class="col-md-3"><div class="form-group"><label>Tanggal Mulai</label><input type="date" name="start_date" id="sched-start" class="form-control" value="' + H.e(s.start_date || '') + '"></div></div><div class="col-md-3"><div class="form-group"><label>Batas Akhir (Due)</label><input type="date" name="due_date" id="sched-due" class="form-control" value="' + H.e(s.due_date || '') + '"></div></div></div><div class="form-group"><label>Status</label><select name="status" class="form-control"><option value="draft"' + (s.status === 'draft' ? ' selected' : '') + '>Draft</option><option value="ongoing"' + (s.status === 'ongoing' ? ' selected' : '') + '>Berjalan</option><option value="completed"' + (s.status === 'completed' ? ' selected' : '') + '>Selesai</option></select></div><div class="form-group"><label>Deskripsi</label><textarea name="description" class="form-control" rows="2" placeholder="Catatan jadwal patching...">' + H.e(s.description || '') + '</textarea></div></div><div class="card-footer"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button> <a href="#patching" class="btn btn-default">Batal</a></div></form></div>';
        const scripts = '<script>(function(){var q=document.getElementById("sched-quarter"),y=document.getElementById("sched-year"),name=document.getElementById("sched-name"),start=document.getElementById("sched-start"),due=document.getElementById("sched-due");function qd(Q,Y){var sm=(Q-1)*3+1;var sd=Y+"-"+String(sm).padStart(2,"0")+"-01";var ed=new Date(Y,sm+2,0).toISOString().slice(0,10);return{start:sd,end:ed};}function up(){var Q=parseInt(q.value),Y=parseInt(y.value);if(!name.value||/^Patching Q[1-4] \\d{4}$/.test(name.value)){name.value="Patching Q"+Q+" "+Y;}if(!start.value||!due.value){var d=qd(Q,Y);if(!start.value)start.value=d.start;if(!due.value)due.value=d.end;}}q.addEventListener("change",up);y.addEventListener("input",up);up();})();<\/script>';
        Views.shell(html, scripts);
    };
    P.saveSchedule = function (e, id) {
        e.preventDefault();
        const fd = new FormData(e.target);
        const name = fd.get('name'); const quarter = parseInt(fd.get('quarter')); const year = parseInt(fd.get('year'));
        const start_date = fd.get('start_date'); const due_date = fd.get('due_date'); const status = fd.get('status'); const description = fd.get('description');
        const data = { name: name, quarter: quarter, year: year, start_date: start_date, due_date: due_date, status: status, description: description };
        if (id) { Store.update('patch_schedules', id, data); H.audit('patching', 'updated', 'Updated schedule ' + name); H.flash('success', Lang.t('schedule_updated')); Router.navigate('patching/' + id); }
        else { const created = Store.insert('patch_schedules', Object.assign({}, data, { created_by: Auth.id() })); H.audit('patching', 'created', 'Created schedule ' + name); H.flash('success', Lang.t('schedule_added')); Router.navigate('patching/' + created.id); }
        return false;
    };

    // ========================================================================
    // PATCHING — SHOW (detail + generate + checklists)
    // ========================================================================
    P.patchShow = function (params) {
        const id = params.id;
        if (!Auth.requireLogin()) return;
        Views.layout(Lang.t('patch_schedule'), 'app');
        const s = Store.get('patch_schedules').find(x => x.id == id);
        if (!s) { Views.error(); return; }
        const checklists = Store.get('patch_checklists').filter(c => c.schedule_id == id);
        const assets = Store.get('assets'); const cats = Store.get('categories');
        checklists.forEach(c => { const a = assets.find(x => x.id == c.asset_id); c.asset_code = a ? a.asset_code : ''; c.asset_name = a ? a.name : ''; c.category_name = a ? (cats.find(cc => cc.id == a.category_id) || {}).name || '' : ''; c.location = a ? a.location : ''; });
        s.total_aset = checklists.length;
        s.done_aset = checklists.filter(c => c.status === 'completed').length;
        s.progress_aset = checklists.filter(c => c.status === 'in_progress').length;
        s.pending_aset = checklists.filter(c => c.status === 'pending').length;
        s.skipped_aset = checklists.filter(c => c.status === 'skipped').length;
        const pct = s.total_aset > 0 ? Math.round((s.done_aset / s.total_aset) * 100) : 0;

        // available IT assets (exclude Umum, not yet in this schedule)
        const umumCat = cats.find(c => c.name === 'Umum');
        const availableAssets = assets.filter(a => !a.deleted_at && a.category_id != (umumCat ? umumCat.id : -1) && !checklists.find(c => c.asset_id == a.id));

        let html = '<div class="row"><div class="col-md-12"><div class="card card-primary card-outline"><div class="card-header"><h3 class="card-title"><i class="fas fa-shield-alt mr-1"></i> ' + H.e(s.name) + '</h3><div class="card-tools"><a href="#patching" class="btn btn-default btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>' + (Auth.isAdmin() ? ' <a href="#patching/' + s.id + '/edit" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Edit</a><form onsubmit="return Pages.deleteSchedule(event,' + s.id + ')" class="d-inline"><button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Hapus</button></form>' : '') + '</div></div><div class="card-body"><div class="row"><div class="col-md-3 text-center"><div class="small-box bg-info"><div class="inner"><h3>' + s.total_aset + '</h3><p>Total Aset</p></div><div class="icon"><i class="fas fa-boxes"></i></div></div></div><div class="col-md-3 text-center"><div class="small-box bg-success"><div class="inner"><h3>' + s.done_aset + '</h3><p>Selesai</p></div><div class="icon"><i class="fas fa-check-circle"></i></div></div></div><div class="col-md-3 text-center"><div class="small-box bg-warning"><div class="inner"><h3>' + (s.progress_aset + s.pending_aset) + '</h3><p>Belum Selesai</p></div><div class="icon"><i class="fas fa-clock"></i></div></div></div><div class="col-md-3 text-center"><div class="small-box bg-dark"><div class="inner"><h3>' + s.skipped_aset + '</h3><p>Skipped</p></div><div class="icon"><i class="fas fa-forward"></i></div></div></div></div><div class="row mt-2"><div class="col-md-8"><dl class="row mb-0"><dt class="col-sm-3">Periode</dt><dd class="col-sm-9">Kuartal ' + s.quarter + ' / ' + s.year + ' (per 3 bulan)</dd><dt class="col-sm-3">Rentang Tanggal</dt><dd class="col-sm-9">' + H.tgl(s.start_date) + ' &rarr; ' + H.tgl(s.due_date) + '</dd><dt class="col-sm-3">Status</dt><dd class="col-sm-9">' + H.patchStatusBadge(s.status) + '</dd>' + (s.description ? '<dt class="col-sm-3">Deskripsi</dt><dd class="col-sm-9">' + H.e(s.description) + '</dd>' : '') + '</dl></div><div class="col-md-4"><label class="small">Progress Keseluruhan: <strong>' + pct + '%</strong></label><div class="progress" style="height:22px"><div class="progress-bar bg-' + (pct >= 100 ? 'success' : (pct > 0 ? 'warning' : 'secondary')) + '" style="width:' + pct + '%">' + pct + '%</div></div></div></div></div></div></div></div>';

        // generate (admin)
        if (Auth.isAdmin() && availableAssets.length > 0) {
            html += '<div class="card card-success card-outline"><div class="card-header"><h3 class="card-title"><i class="fas fa-plus-circle mr-1"></i> Generate Checklist Aset IT</h3><div class="card-tools"><form onsubmit="return Pages.generateAll(event,' + s.id + ')" class="d-inline"><button type="submit" class="btn btn-success btn-sm"><i class="fas fa-magic"></i> Generate Semua Aset IT</button></form></div></div><form onsubmit="return Pages.generateSelected(event,' + s.id + ')"><div class="card-body"><p class="text-muted small">Pilih aset IT untuk dibuatkan checklist patching:</p><div class="table-responsive" style="max-height:280px;overflow-y:auto"><table class="table table-sm table-hover"><thead><tr><th width="40"><input type="checkbox" id="chk-all" checked></th><th>Kode</th><th>Nama</th><th>Kategori</th><th>Lokasi</th></tr></thead><tbody>';
            availableAssets.forEach(a => {
                html += '<tr><td><input type="checkbox" name="asset_ids[]" value="' + a.id + '" class="chk-asset" checked></td><td class="asset-code">' + H.e(a.asset_code) + '</td><td>' + H.e(a.name) + '</td><td><span class="badge badge-light">' + H.e((cats.find(c => c.id == a.category_id) || {}).name || '') + '</span></td><td>' + H.e(a.location || '-') + '</td></tr>';
            });
            html += '</tbody></table></div></div><div class="card-footer"><button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Buat Checklist Terpilih</button></div></form></div>';
        }

        // checklists table
        html += '<div class="card card-info card-outline"><div class="card-header"><h3 class="card-title"><i class="fas fa-list-check mr-1"></i> ' + Lang.t('checklists') + ' (' + checklists.length + ')</h3><div class="card-tools"><a href="#patching/' + s.id + '/computers" class="btn btn-info btn-sm"><i class="fas fa-laptop-code"></i> ' + Lang.t('view_patch_list') + '</a></div></div><div class="card-body p-0">';
        if (checklists.length === 0) html += '<div class="empty-state"><i class="fas fa-clipboard-list"></i><p class="mt-3">Belum ada checklist. ' + (Auth.isAdmin() ? 'Generate dari aset IT di atas.' : 'Hubungi admin.') + '</p></div>';
        else {
            html += '<div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>Kode</th><th>Nama Aset</th><th>Kategori</th><th>Lokasi</th><th>Status</th><th class="text-center">Progress</th><th class="text-center">Aksi</th></tr></thead><tbody>';
            checklists.forEach(c => {
                const p = checklistProgress(c.id);
                const ip = p.total > 0 ? Math.round((p.done / p.total) * 100) : 0;
                html += '<tr><td class="asset-code">' + H.e(c.asset_code) + '</td><td>' + H.e(c.asset_name) + '</td><td><span class="badge badge-light">' + H.e(c.category_name) + '</span></td><td>' + H.e(c.location || '-') + '</td><td>' + H.patchStatusBadge(c.status) + '</td><td class="text-center"><div style="min-width:100px"><div class="progress progress-sm mb-1"><div class="progress-bar bg-' + (ip >= 100 ? 'success' : (ip > 0 ? 'warning' : 'secondary')) + '" style="width:' + ip + '%"></div></div><small>' + p.done + '/' + p.total + '</small></div></td><td class="text-center"><a href="#patching/checklist/' + c.id + '" class="btn btn-primary btn-sm"><i class="fas fa-clipboard-check"></i> Ceklis</a>' + (Auth.isAdmin() ? '<form onsubmit="return Pages.deleteChecklist(event,' + c.id + ')" class="d-inline"><button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button></form>' : '') + '</td></tr>';
            });
            html += '</tbody></table></div>';
        }
        html += '</div></div>';
        Views.shell(html);
    };
    P.generateSelected = function (e, scheduleId) {
        e.preventDefault();
        const fd = new FormData(e.target);
        const ids = fd.getAll('asset_ids[]').map(Number);
        if (ids.length === 0) { H.flash('error', Lang.t('select_one_asset')); return false; }
        let count = 0;
        ids.forEach(aid => {
            const existing = Store.get('patch_checklists').find(c => c.schedule_id == scheduleId && c.asset_id == aid);
            if (existing) return;
            const cl = Store.insert('patch_checklists', { schedule_id: scheduleId, asset_id: aid, status: 'pending', patched_by: null, patched_at: null, notes: '' });
            Store.get('patch_items').forEach(it => {
                Store.insert('patch_checklist_items', { checklist_id: cl.id, item_id: it.id, is_checked: 0, checked_by: null, checked_at: null, notes: '', patch_code: '' });
            });
            count++;
        });
        refreshScheduleStatus(scheduleId);
        H.audit('patching', 'generated', 'Generated ' + count + ' checklists for schedule ' + scheduleId);
        H.flash('success', Lang.t('checklists_generated', { count: count }));
        Router.navigate('patching/' + scheduleId);
        return false;
    };
    P.generateAll = function (e, scheduleId) {
        e.preventDefault();
        const cats = Store.get('categories'); const umum = cats.find(c => c.name === 'Umum');
        const assets = Store.get('assets').filter(a => !a.deleted_at && a.category_id != (umum ? umum.id : -1));
        const checklists = Store.get('patch_checklists').filter(c => c.schedule_id == scheduleId);
        let count = 0;
        assets.forEach(a => {
            if (checklists.find(c => c.asset_id == a.id)) return;
            const cl = Store.insert('patch_checklists', { schedule_id: scheduleId, asset_id: a.id, status: 'pending', patched_by: null, patched_at: null, notes: '' });
            Store.get('patch_items').forEach(it => { Store.insert('patch_checklist_items', { checklist_id: cl.id, item_id: it.id, is_checked: 0, checked_by: null, checked_at: null, notes: '', patch_code: '' }); });
            count++;
        });
        refreshScheduleStatus(scheduleId);
        H.audit('patching', 'generated_all', 'Generated all ' + count + ' IT checklists for schedule ' + scheduleId);
        H.flash('success', Lang.t('it_checklists_generated', { count: count }));
        Router.navigate('patching/' + scheduleId);
        return false;
    };
    P.deleteSchedule = function (e, id) {
        e.preventDefault();
        if (!confirm(Lang.t('delete_schedule_confirm'))) return false;
        const cls = Store.get('patch_checklists').filter(c => c.schedule_id == id);
        cls.forEach(c => { Store.remove('patch_checklists', c.id); Store.get('patch_checklist_items').filter(i => i.checklist_id == c.id).forEach(i => Store.remove('patch_checklist_items', i.id)); });
        Store.remove('patch_schedules', id);
        H.audit('patching', 'deleted', 'Deleted schedule ' + id);
        H.flash('success', Lang.t('schedule_deleted'));
        Router.navigate('patching');
        return false;
    };
    P.deleteChecklist = function (e, id) {
        e.preventDefault();
        if (!confirm(Lang.t('delete_checklist_confirm'))) return false;
        const cl = Store.get('patch_checklists').find(c => c.id == id);
        Store.get('patch_checklist_items').filter(i => i.checklist_id == id).forEach(i => Store.remove('patch_checklist_items', i.id));
        Store.remove('patch_checklists', id);
        if (cl) refreshScheduleStatus(cl.schedule_id);
        H.audit('patching', 'checklist_deleted', 'Removed checklist ' + id);
        H.flash('success', Lang.t('checklist_deleted'));
        Router.navigate(Router.current().path);
        return false;
    };

    // ========================================================================
    // PATCHING — CHECKLIST (per asset, toggle items)
    // ========================================================================
    P.patchChecklist = function (params) {
        const id = params.id;
        if (!Auth.requireLogin()) return;
        Views.layout(Lang.t('checklist'), 'app');
        const c = Store.get('patch_checklists').find(x => x.id == id);
        if (!c) { Views.error(); return; }
        const sch = Store.get('patch_schedules').find(x => x.id == c.schedule_id);
        const asset = Store.get('assets').find(x => x.id == c.asset_id);
        const catName = asset ? (Store.get('categories').find(cc => cc.id == asset.category_id) || {}).name || '' : '';
        const items = Store.get('patch_items').sort((a, b) => a.sort_order - b.sort_order);
        const cli = Store.get('patch_checklist_items').filter(i => i.checklist_id == id);
        const users = Store.get('users');
        const progress = checklistProgress(id);

        let html = '<div class="row"><div class="col-md-4"><div class="card card-primary card-outline"><div class="card-header"><h3 class="card-title"><i class="fas fa-box mr-1"></i> Aset</h3></div><div class="card-body"><div class="text-center mb-3"><div class="d-inline-flex align-items-center justify-content-center bg-primary mb-2" style="width:70px;height:70px;border-radius:12px"><i class="fas fa-desktop text-white" style="font-size:2rem"></i></div><h5 class="mb-0">' + H.e(asset ? asset.name : '') + '</h5><p class="text-muted asset-code mb-0">' + H.e(asset ? asset.asset_code : '') + '</p>' + H.patchStatusBadge(c.status) + '</div><dl class="row mb-0"><dt class="col-sm-4">Kategori</dt><dd class="col-sm-8">' + H.e(catName) + '</dd><dt class="col-sm-4">Lokasi</dt><dd class="col-sm-8">' + H.e(asset ? (asset.location || '-') : '-') + '</dd><dt class="col-sm-4">Brand</dt><dd class="col-sm-8"><small>' + H.e(asset ? (asset.brand_spec || '-') : '-') + '</small></dd></dl></div></div><div class="card card-info card-outline"><div class="card-header"><h3 class="card-title"><i class="fas fa-chart-bar mr-1"></i> Progress</h3></div><div class="card-body text-center"><h2 class="text-' + (progress.total > 0 && progress.done == progress.total ? 'success' : 'primary') + '">' + (progress.total > 0 ? Math.round((progress.done / progress.total) * 100) : 0) + '%</h2><div class="progress mb-2" style="height:20px"><div class="progress-bar bg-' + (progress.total > 0 && progress.done == progress.total ? 'success' : 'primary') + '" style="width:' + (progress.total > 0 ? Math.round((progress.done / progress.total) * 100) : 0) + '%">' + progress.done + '/' + progress.total + '</div></div><p class="text-muted small mb-0">' + progress.done + ' dari ' + progress.total + ' item selesai</p>' + (c.patched_by ? '<p class="text-muted small">Dikerjakan oleh: <strong>' + H.e((users.find(u => u.id == c.patched_by) || {}).name || '') + '</strong></p>' : '') + '</div></div><div class="card card-warning card-outline"><div class="card-header"><h3 class="card-title"><i class="fas fa-cog mr-1"></i> Aksi</h3></div><div class="card-body"><a href="#patching/' + sch.id + '" class="btn btn-default btn-block"><i class="fas fa-arrow-left"></i> Kembali ke Jadwal</a>' + (c.status !== 'completed' ? '<form onsubmit="return Pages.skipChecklist(event,' + c.id + ')" class="mt-2"><input type="hidden" name="status" value="skipped"><div class="form-group"><input type="text" name="note" class="form-control form-control-sm" placeholder="Alasan skip (opsional)"></div><button class="btn btn-dark btn-block btn-sm"><i class="fas fa-forward"></i> Skip Aset Ini</button></form>' : '') + (Auth.isAdmin() && c.status !== 'pending' ? '<form onsubmit="return Pages.resetChecklist(event,' + c.id + ')" class="mt-2"><input type="hidden" name="status" value="pending"><button class="btn btn-secondary btn-block btn-sm"><i class="fas fa-undo"></i> Reset Checklist</button></form>' : '') + '</div></div></div>';

        // checklist items
        html += '<div class="col-md-8"><div class="card card-success card-outline"><div class="card-header"><h3 class="card-title"><i class="fas fa-clipboard-check mr-1"></i> Checklist Patching</h3><span class="text-muted small">Jadwal: ' + H.e(sch.name) + '</span></div><div class="card-body"><p class="text-muted small">Centang setiap item setelah dilakukan. Checklist otomatis berstatus <strong>Selesai</strong> saat semua item tercentang.</p>';
        if (items.length === 0) html += '<div class="empty-state"><i class="fas fa-list-check"></i><p class="mt-3">Belum ada item checklist.</p></div>';
        else {
            html += '<div class="list-group">';
            items.forEach(it => {
                const row = cli.find(i => i.item_id == it.id) || { is_checked: 0, patch_code: '', checked_by: null, checked_at: null };
                const checked = row.is_checked == 1;
                const checkerName = row.checked_by ? (users.find(u => u.id == row.checked_by) || {}).name || '' : '';
                html += '<div class="list-group-item list-group-item-action ' + (checked ? 'list-group-item-success' : '') + '"><div class="d-flex align-items-start"><div class="mr-3 pt-1"><form onsubmit="return Pages.toggleItem(event,' + c.id + ',' + it.id + ')" class="d-inline"><input type="hidden" name="checked" value="' + (checked ? '0' : '1') + '"><button type="submit" class="btn btn-sm btn-toggle-' + (checked ? 'success' : 'outline-secondary') + '" style="font-size:1.1rem"><i class="fas fa-' + (checked ? 'check-square' : 'square') + '"></i></button></form></div><div class="flex-grow-1"><div class="d-flex justify-content-between align-items-start"><div><h6 class="mb-0 ' + (checked ? 'text-success' : '') + '">' + H.e(it.name) + (checked ? ' <i class="fas fa-check-circle text-success ml-1"></i>' : '') + '</h6><small class="text-muted">' + H.e(it.description || '') + '</small></div>' + (row.patch_code ? '<span class="badge badge-info ml-2" title="' + Lang.t('patch_code') + '"><i class="fas fa-tag mr-1"></i>' + H.e(row.patch_code) + '</span>' : '') + '</div>' + (checked && checkerName ? '<div class="text-muted small"><i class="far fa-clock"></i> ' + H.tglwaktu(row.checked_at) + ' — ' + H.e(checkerName) + '</div>' : '') + '<div class="input-group input-group-sm mt-2 patch-code-group" style="max-width:340px"><div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-code"></i> ' + Lang.t('patch_code') + '</span></div><input type="text" class="form-control patch-code-input" placeholder="' + Lang.t('patch_code_placeholder') + '" value="' + H.e(row.patch_code || '') + '" data-item="' + it.id + '"><div class="input-group-append"><button type="button" class="btn btn-outline-primary btn-save-code" onclick="Pages.saveCode(' + c.id + ',' + it.id + ')"><i class="fas fa-save"></i></button></div></div></div></div></div>';
            });
            html += '</div>';
        }
        html += '</div></div></div></div>';
        Views.shell(html);
    };
    P.toggleItem = function (e, checklistId, itemId) {
        e.preventDefault();
        const checked = new FormData(e.target).get('checked') == '1';
        const cli = Store.get('patch_checklist_items').find(i => i.checklist_id == checklistId && i.item_id == itemId);
        // include patch code from input
        const codeInput = document.querySelector('.patch-code-input[data-item="' + itemId + '"]');
        const patchCode = codeInput ? codeInput.value.trim() : '';
        if (cli) { Store.update('patch_checklist_items', cli.id, { is_checked: checked ? 1 : 0, checked_by: checked ? Auth.id() : null, checked_at: checked ? new Date().toISOString() : null, patch_code: patchCode }); }
        else { Store.insert('patch_checklist_items', { checklist_id: checklistId, item_id: itemId, is_checked: checked ? 1 : 0, checked_by: checked ? Auth.id() : null, checked_at: checked ? new Date().toISOString() : null, patch_code: patchCode }); }
        refreshChecklistStatus(checklistId);
        const cl = Store.get('patch_checklists').find(c => c.id == checklistId);
        const a = Store.get('assets').find(x => x.id == cl.asset_id);
        if (checked && cl.status === 'completed') { H.assetLog(a.id, 'patching', 'Patching checklist completed'); }
        Router.navigate('patching/checklist/' + checklistId);
        return false;
    };
    P.saveCode = function (checklistId, itemId) {
        const input = document.querySelector('.patch-code-input[data-item="' + itemId + '"]');
        const code = input ? input.value.trim() : '';
        const cli = Store.get('patch_checklist_items').find(i => i.checklist_id == checklistId && i.item_id == itemId);
        if (cli) { Store.update('patch_checklist_items', cli.id, { patch_code: code }); }
        else { Store.insert('patch_checklist_items', { checklist_id: checklistId, item_id: itemId, is_checked: 0, checked_by: null, checked_at: null, patch_code: code }); }
        H.audit('patching', 'code_saved', 'Saved patch code for item ' + itemId);
        // visual feedback
        const btn = input.parentElement.querySelector('.btn-save-code');
        btn.classList.remove('btn-outline-primary'); btn.classList.add('btn-success');
        setTimeout(() => { btn.classList.remove('btn-success'); btn.classList.add('btn-outline-primary'); }, 1200);
    };
    P.skipChecklist = function (e, id) {
        e.preventDefault();
        const fd = new FormData(e.target);
        Store.update('patch_checklists', id, { status: 'skipped', notes: fd.get('note') || '' });
        const cl = Store.get('patch_checklists').find(c => c.id == id);
        if (cl) refreshScheduleStatus(cl.schedule_id);
        H.audit('patching', 'skipped', 'Skipped checklist ' + id);
        Router.navigate('patching/' + (cl ? cl.schedule_id : ''));
        return false;
    };
    P.resetChecklist = function (e, id) {
        e.preventDefault();
        if (!confirm(Lang.t('reset_confirm'))) return false;
        Store.update('patch_checklists', id, { status: 'pending', patched_by: null, patched_at: null });
        const items = Store.get('patch_checklist_items').filter(i => i.checklist_id == id);
        items.forEach(i => Store.update('patch_checklist_items', i.id, { is_checked: 0, checked_by: null, checked_at: null }));
        const cl = Store.get('patch_checklists').find(c => c.id == id);
        if (cl) refreshScheduleStatus(cl.schedule_id);
        H.audit('patching', 'reset', 'Reset checklist ' + id);
        Router.navigate('patching/checklist/' + id);
        return false;
    };

    // ========================================================================
    // PATCHING — COMPUTERS (matrix of patch codes)
    // ========================================================================
    P.patchComputers = function (params) {
        const id = params.id;
        if (!Auth.requireLogin()) return;
        Views.layout(Lang.t('computer_patch_list'), 'app');
        const sch = Store.get('patch_schedules').find(x => x.id == id);
        const checklists = Store.get('patch_checklists').filter(c => c.schedule_id == id);
        const assets = Store.get('assets'); const cats = Store.get('categories');
        const items = Store.get('patch_items').sort((a, b) => a.sort_order - b.sort_order);
        const computers = checklists.map(c => {
            const a = assets.find(x => x.id == c.asset_id);
            const cli = Store.get('patch_checklist_items').filter(i => i.checklist_id == c.id);
            const patch_codes = items.map(it => {
                const row = cli.find(i => i.item_id == it.id) || { is_checked: 0, patch_code: '' };
                return { item_name: it.name, is_checked: row.is_checked, patch_code: row.patch_code };
            });
            return {
                asset_code: a ? a.asset_code : '', asset_name: a ? a.name : '', category_name: a ? (cats.find(cc => cc.id == a.category_id) || {}).name || '' : '',
                brand_spec: a ? a.brand_spec : '', location: a ? a.location : '', status: c.status, patch_codes: patch_codes,
            };
        });
        let html = '<div class="card card-primary card-outline"><div class="card-header"><h3 class="card-title"><i class="fas fa-laptop-code mr-1"></i> ' + Lang.t('computer_patch_list') + ' (' + computers.length + ')</h3><div class="card-tools"><a href="#patching/' + id + '" class="btn btn-default btn-sm"><i class="fas fa-arrow-left"></i> ' + Lang.t('back') + '</a></div></div><div class="card-body"><p class="text-muted small mb-3"><i class="fas fa-info-circle"></i> ' + Lang.t('patch_code_placeholder') + '</p>';
        if (computers.length === 0) html += '<div class="empty-state"><i class="fas fa-laptop"></i><p class="mt-3">' + Lang.t('no_checklists') + '</p></div>';
        else {
            html += '<div class="table-responsive"><table class="table table-bordered table-hover"><thead class="thead-dark"><tr><th width="40">#</th><th>' + Lang.t('asset_code') + '</th><th>' + Lang.t('computer') + ' / ' + Lang.t('assets') + '</th><th>' + Lang.t('location') + '</th><th>Status</th><th>' + Lang.t('patch_codes') + '</th><th class="text-center">' + Lang.t('patched_items') + '</th></tr></thead><tbody>';
            computers.forEach((c, idx) => {
                const patchedCount = c.patch_codes.filter(p => p.is_checked == 1).length;
                html += '<tr><td>' + (idx + 1) + '</td><td class="asset-code">' + H.e(c.asset_code) + '</td><td><strong>' + H.e(c.asset_name) + '</strong><br><small class="text-muted">' + H.e(c.category_name) + ' • ' + H.e(c.brand_spec || '') + '</small></td><td>' + H.e(c.location || '-') + '</td><td>' + H.patchStatusBadge(c.status) + '</td><td><div class="d-flex flex-wrap" style="gap:4px">' + c.patch_codes.map(p => p.patch_code ? '<span class="badge badge-' + (p.is_checked ? 'success' : 'info') + '" title="' + H.e(p.item_name) + '"><i class="fas fa-tag mr-1"></i>' + H.e(p.patch_code) + '</span>' : '').join('') + '</div></td><td class="text-center"><span class="badge badge-' + (patchedCount > 0 ? 'primary' : 'secondary') + '">' + patchedCount + '/' + c.patch_codes.length + '</span></td></tr>';
            });
            html += '</tbody></table></div><hr><h5 class="mb-3"><i class="fas fa-list-ul mr-1"></i> ' + Lang.t('patched_items') + ' — ' + Lang.t('patch_codes') + '</h5><div class="table-responsive"><table class="table table-sm table-striped"><thead><tr><th>' + Lang.t('computer') + '</th>' + items.map(it => '<th class="text-center" title="' + H.e(it.name) + '">' + H.e(it.name) + '</th>').join('') + '</tr></thead><tbody>';
            computers.forEach(c => {
                html += '<tr><td><span class="asset-code">' + H.e(c.asset_code) + '</span><br><small>' + H.e(c.asset_name) + '</small></td>';
                items.forEach(it => {
                    const pc = c.patch_codes.find(p => p.item_name === it.name);
                    if (pc && pc.patch_code) html += '<td class="text-center"><span class="badge badge-' + (pc.is_checked ? 'success' : 'info') + '">' + H.e(pc.patch_code) + '</span>' + (pc.is_checked ? '<br><i class="fas fa-check text-success"></i>' : '') + '</td>';
                    else html += '<td class="text-center"><span class="text-muted">—</span></td>';
                });
                html += '</tr>';
            });
            html += '</tbody></table></div>';
        }
        html += '</div></div>';
        Views.shell(html);
    };

    // ========================================================================
    // REPORTS (tabs + print)
    // ========================================================================
    P.reports = function (query) {
        if (!Auth.requireLogin()) return;
        Views.layout(Lang.t('asset_report'), 'app');
        const tab = query.tab || 'summary';
        const f = { category_id: query.category_id || '', status: query.status || '', location: query.location || '', date_from: query.date_from || '', date_to: query.date_to || '' };
        const cats = Store.get('categories');
        let assets = Store.get('assets').filter(a => !a.deleted_at);
        assets.forEach(a => { a.category_name = (cats.find(c => c.id == a.category_id) || {}).name || ''; });
        if (f.category_id) assets = assets.filter(a => String(a.category_id) === String(f.category_id));
        if (f.status) assets = assets.filter(a => a.status === f.status);
        if (f.location) assets = assets.filter(a => (a.location || '').toLowerCase().includes(f.location.toLowerCase()));
        if (f.date_from) assets = assets.filter(a => a.purchase_date && a.purchase_date >= f.date_from);
        if (f.date_to) assets = assets.filter(a => a.purchase_date && a.purchase_date <= f.date_to);

        const summary = {
            total: assets.length,
            tersedia: assets.filter(a => a.status === 'tersedia').length,
            dipinjam: assets.filter(a => a.status === 'dipinjam').length,
            rusak: assets.filter(a => a.status === 'rusak').length,
            nilai_total: assets.reduce((s, a) => s + Number(a.price || 0), 0),
            nilai_tersedia: assets.filter(a => a.status === 'tersedia').reduce((s, a) => s + Number(a.price || 0), 0),
            nilai_dipinjam: assets.filter(a => a.status === 'dipinjam').reduce((s, a) => s + Number(a.price || 0), 0),
            nilai_rusak: assets.filter(a => a.status === 'rusak').reduce((s, a) => s + Number(a.price || 0), 0),
        };
        const locations = [...new Set(Store.get('assets').filter(a => !a.deleted_at && a.location).map(a => a.location))];
        const byCategory = cats.map(c => {
            const items = assets.filter(a => a.category_id == c.id);
            return { category_name: c.name, tersedia: items.filter(a => a.status === 'tersedia').length, dipinjam: items.filter(a => a.status === 'dipinjam').length, rusak: items.filter(a => a.status === 'rusak').length, total: items.length, nilai: items.reduce((s, a) => s + Number(a.price || 0), 0) };
        }).filter(c => c.total > 0);
        const locMap = {};
        assets.forEach(a => { const l = a.location || 'N/A'; if (!locMap[l]) locMap[l] = { location: l, tersedia: 0, dipinjam: 0, rusak: 0, total: 0, nilai: 0 }; locMap[l][a.status]++; locMap[l].total++; locMap[l].nilai += Number(a.price || 0); });
        const byLocation = Object.values(locMap);

        const qs = new URLSearchParams(Object.assign({}, f, { tab: tab })).toString();

        let html = '<div class="card card-primary card-outline"><div class="card-header"><h3 class="card-title"><i class="fas fa-file-alt mr-1"></i> Laporan Aset</h3><div class="card-tools"><a href="#reports/print?' + qs + '" class="btn btn-success btn-sm"><i class="fas fa-print"></i> Cetak / PDF</a></div></div><div class="card-body">';
        // filter
        html += '<form onsubmit="return Pages.filterReports(event)" class="search-bar mb-3"><input type="hidden" name="tab" value="' + tab + '"><div class="row"><div class="col-md-3"><label class="small text-muted">Kategori</label><select name="category_id" class="form-control form-control-sm"><option value="">Semua</option>' + cats.map(c => '<option value="' + c.id + '"' + (String(f.category_id) === String(c.id) ? ' selected' : '') + '>' + H.e(c.name) + '</option>').join('') + '</select></div><div class="col-md-2"><label class="small text-muted">Status</label><select name="status" class="form-control form-control-sm"><option value="">Semua</option><option value="tersedia"' + (f.status === 'tersedia' ? ' selected' : '') + '>Tersedia</option><option value="dipinjam"' + (f.status === 'dipinjam' ? ' selected' : '') + '>Dipinjam</option><option value="rusak"' + (f.status === 'rusak' ? ' selected' : '') + '>Rusak</option></select></div><div class="col-md-2"><label class="small text-muted">Lokasi</label><input type="text" name="location" class="form-control form-control-sm" list="loc-list" value="' + H.e(f.location) + '"><datalist id="loc-list">' + locations.map(l => '<option value="' + H.e(l) + '">').join('') + '</datalist></div><div class="col-md-2"><label class="small text-muted">Tgl. Beli Dari</label><input type="date" name="date_from" class="form-control form-control-sm" value="' + H.e(f.date_from) + '"></div><div class="col-md-2"><label class="small text-muted">Tgl. Beli Sampai</label><input type="date" name="date_to" class="form-control form-control-sm" value="' + H.e(f.date_to) + '"></div><div class="col-md-1 d-flex align-items-end"><button type="submit" class="btn btn-primary btn-block btn-sm"><i class="fas fa-filter"></i></button></div></div></form>';
        // tabs
        const tabs = [['summary', 'Ringkasan'], ['category', 'Per Kategori'], ['location', 'Per Lokasi'], ['detail', 'Detail Aset']];
        const baseQs = new URLSearchParams(f).toString();
        html += '<ul class="nav nav-pills mb-3">' + tabs.map(t => '<li class="nav-item"><a class="nav-link ' + (tab === t[0] ? 'active' : '') + '" href="#reports?' + (baseQs ? baseQs + '&' : '') + 'tab=' + t[0] + '">' + t[1] + '</a></li>').join('') + '</ul>';

        if (tab === 'summary') {
            html += '<div class="row"><div class="col-md-3 col-sm-6"><div class="small-box bg-info"><div class="inner"><h3>' + summary.total + '</h3><p>Total Aset</p></div><div class="icon"><i class="fas fa-boxes"></i></div></div></div><div class="col-md-3 col-sm-6"><div class="small-box bg-success"><div class="inner"><h3>' + summary.tersedia + '</h3><p>Tersedia</p></div><div class="icon"><i class="fas fa-check-circle"></i></div></div></div><div class="col-md-3 col-sm-6"><div class="small-box bg-warning"><div class="inner"><h3>' + summary.dipinjam + '</h3><p>Dipinjam</p></div><div class="icon"><i class="fas fa-hand-paper"></i></div></div></div><div class="col-md-3 col-sm-6"><div class="small-box bg-danger"><div class="inner"><h3>' + summary.rusak + '</h3><p>Rusak</p></div><div class="icon"><i class="fas fa-tools"></i></div></div></div></div>';
            html += '<div class="row"><div class="col-md-7"><div class="card card-outline card-primary"><div class="card-header"><h6 class="card-title">Distribusi per Kategori</h6></div><div class="card-body"><div id="rep-chart-category"></div></div></div></div><div class="col-md-5"><div class="card card-outline card-success"><div class="card-header"><h6 class="card-title">Komposisi Status</h6></div><div class="card-body"><div id="rep-chart-status"></div></div></div></div></div>';
            html += '<div class="card card-outline card-info"><div class="card-header"><h6 class="card-title">Ringkasan Nilai Aset</h6></div><div class="card-body p-0"><table class="table table-striped mb-0"><thead><tr><th>Status</th><th class="text-center">Total</th>' + (H.priceVisible() ? '<th class="text-right">Total Nilai</th>' : '') + '</tr></thead><tbody><tr><td>Tersedia</td><td class="text-center">' + summary.tersedia + '</td>' + (H.priceVisible() ? '<td class="text-right">' + H.rp(summary.nilai_tersedia) + '</td>' : '') + '</tr><tr><td>Dipinjam</td><td class="text-center">' + summary.dipinjam + '</td>' + (H.priceVisible() ? '<td class="text-right">' + H.rp(summary.nilai_dipinjam) + '</td>' : '') + '</tr><tr><td>Rusak</td><td class="text-center">' + summary.rusak + '</td>' + (H.priceVisible() ? '<td class="text-right">' + H.rp(summary.nilai_rusak) + '</td>' : '') + '</tr></tbody><tfoot><tr><th>Total</th><th class="text-center">' + summary.total + '</th>' + (H.priceVisible() ? '<th class="text-right">' + H.rp(summary.nilai_total) + '</th>' : '') + '</tr></tfoot></table></div></div>';
        } else if (tab === 'category') {
            html += '<div class="table-responsive"><table class="table table-hover"><thead><tr><th>#</th><th>Kategori</th><th class="text-center">Tersedia</th><th class="text-center">Dipinjam</th><th class="text-center">Rusak</th><th class="text-center">Total</th>' + (H.priceVisible() ? '<th class="text-right">Nilai</th>' : '') + '</tr></thead><tbody>';
            byCategory.forEach((c, i) => { html += '<tr><td>' + (i + 1) + '</td><td>' + H.e(c.category_name) + '</td><td class="text-center">' + c.tersedia + '</td><td class="text-center">' + c.dipinjam + '</td><td class="text-center">' + c.rusak + '</td><td class="text-center">' + c.total + '</td>' + (H.priceVisible() ? '<td class="text-right">' + H.rp(c.nilai) + '</td>' : '') + '</tr>'; });
            html += '</tbody></table></div>';
        } else if (tab === 'location') {
            html += '<div class="table-responsive"><table class="table table-hover"><thead><tr><th>#</th><th>Lokasi</th><th class="text-center">Tersedia</th><th class="text-center">Dipinjam</th><th class="text-center">Rusak</th><th class="text-center">Total</th>' + (H.priceVisible() ? '<th class="text-right">Nilai</th>' : '') + '</tr></thead><tbody>';
            byLocation.forEach((l, i) => { html += '<tr><td>' + (i + 1) + '</td><td>' + H.e(l.location) + '</td><td class="text-center">' + l.tersedia + '</td><td class="text-center">' + l.dipinjam + '</td><td class="text-center">' + l.rusak + '</td><td class="text-center">' + l.total + '</td>' + (H.priceVisible() ? '<td class="text-right">' + H.rp(l.nilai) + '</td>' : '') + '</tr>'; });
            html += '</tbody></table></div>';
        } else {
            html += '<div class="table-responsive"><table class="table table-hover"><thead><tr><th width="40">Foto</th><th>Kode</th><th>Nama</th><th>Kategori</th><th>Lokasi</th><th>Tgl. Beli</th><th>Status</th>' + (H.priceVisible() ? '<th class="text-right">Harga</th>' : '') + '</tr></thead><tbody>';
            assets.forEach(a => { html += '<tr><td>' + H.assetPhotoImg(a.photo, 30) + '</td><td class="asset-code">' + H.e(a.asset_code) + '</td><td>' + H.e(a.name) + '<br><small class="text-muted">' + H.e(a.brand_spec || '') + '</small></td><td>' + H.e(a.category_name) + '</td><td>' + H.e(a.location || '-') + '</td><td>' + H.tgl(a.purchase_date) + '</td><td>' + (a.status.charAt(0).toUpperCase() + a.status.slice(1)) + '</td>' + (H.priceVisible() ? '<td class="text-right">' + H.rp(a.price) + '</td>' : '') + '</tr>'; });
            html += '</tbody></table></div>';
        }
        html += '</div></div>';

        // chart scripts only for summary
        let scripts = '';
        if (tab === 'summary') {
            const catLab = JSON.stringify(byCategory.map(c => c.category_name));
            const catTot = JSON.stringify(byCategory.map(c => c.total));
            scripts = '<script>$(function(){new ApexCharts(document.querySelector("#rep-chart-category"),{chart:{type:"bar",height:300,toolbar:{show:false},fontFamily:"Source Sans Pro"},plotOptions:{bar:{borderRadius:6}},series:[{name:"Total",data:' + catTot + '}],colors:["#3c5184"],xaxis:{categories:' + catLab + '},dataLabels:{enabled:true}}).render();new ApexCharts(document.querySelector("#rep-chart-status"),{chart:{type:"donut",height:300,fontFamily:"Source Sans Pro"},series:[' + summary.tersedia + ',' + summary.dipinjam + ',' + summary.rusak + '],labels:["Tersedia","Dipinjam","Rusak"],colors:["#28a745","#ffc107","#dc3545"],legend:{position:"bottom"},plotOptions:{pie:{donut:{size:"62%"}}}}).render();});<\/script>';
        }
        Views.shell(html, scripts);
    };
    P.filterReports = function (e) {
        e.preventDefault();
        const fd = new FormData(e.target);
        const p = new URLSearchParams();
        for (const [k, v] of fd.entries()) if (v) p.set(k, v);
        Router.navigate('reports?' + p.toString());
        return false;
    };

    // ========================================================================
    // REPORTS — PRINT
    // ========================================================================
    P.reportPrint = function (query) {
        if (!Auth.requireLogin()) return;
        Views.layout(Lang.t('asset_report'), 'print');
        const f = { category_id: query.category_id || '', status: query.status || '', location: query.location || '', date_from: query.date_from || '', date_to: query.date_to || '' };
        const cats = Store.get('categories');
        let assets = Store.get('assets').filter(a => !a.deleted_at);
        assets.forEach(a => { a.category_name = (cats.find(c => c.id == a.category_id) || {}).name || ''; });
        if (f.category_id) assets = assets.filter(a => String(a.category_id) === String(f.category_id));
        if (f.status) assets = assets.filter(a => a.status === f.status);
        if (f.location) assets = assets.filter(a => (a.location || '').toLowerCase().includes(f.location.toLowerCase()));
        if (f.date_from) assets = assets.filter(a => a.purchase_date && a.purchase_date >= f.date_from);
        if (f.date_to) assets = assets.filter(a => a.purchase_date && a.purchase_date <= f.date_to);
        const summary = {
            total: assets.length, tersedia: assets.filter(a => a.status === 'tersedia').length, dipinjam: assets.filter(a => a.status === 'dipinjam').length, rusak: assets.filter(a => a.status === 'rusak').length,
            nilai_total: assets.reduce((s, a) => s + Number(a.price || 0), 0), nilai_tersedia: assets.filter(a => a.status === 'tersedia').reduce((s, a) => s + Number(a.price || 0), 0), nilai_dipinjam: assets.filter(a => a.status === 'dipinjam').reduce((s, a) => s + Number(a.price || 0), 0), nilai_rusak: assets.filter(a => a.status === 'rusak').reduce((s, a) => s + Number(a.price || 0), 0),
        };
        const byCategory = cats.map(c => { const items = assets.filter(a => a.category_id == c.id); return { category_name: c.name, tersedia: items.filter(a => a.status === 'tersedia').length, dipinjam: items.filter(a => a.status === 'dipinjam').length, rusak: items.filter(a => a.status === 'rusak').length, total: items.length, nilai: items.reduce((s, a) => s + Number(a.price || 0), 0) }; }).filter(c => c.total > 0);
        const locMap = {};
        assets.forEach(a => { const l = a.location || 'N/A'; if (!locMap[l]) locMap[l] = { location: l, tersedia: 0, dipinjam: 0, rusak: 0, total: 0, nilai: 0 }; locMap[l][a.status]++; locMap[l].total++; locMap[l].nilai += Number(a.price || 0); });
        const byLocation = Object.values(locMap);
        const filterDesc = (f.category_id ? 'Kategori: ' + (cats.find(c => c.id == f.category_id) || {}).name + ', ' : '') + (f.status ? 'Status: ' + f.status + ', ' : '') + (f.location ? 'Lokasi: ' + f.location + ', ' : '') + (f.date_from || f.date_to ? 'Beli: ' + (f.date_from || '...') + ' s/d ' + (f.date_to || '...') : '') || 'Semua aset (tanpa filter)';

        let html = '<div class="report-header"><h2><i class="fas fa-cubes"></i> ' + H.e(Setting.companyName()) + ' — ' + Lang.t('asset_report') + '</h2>';
        const addr = Setting.companyAddress(); const phone = Setting.companyPhone(); const cemail = Setting.companyEmail();
        if (addr || phone || cemail) html += '<p class="subtitle">' + H.e(addr) + (phone ? ' &middot; <i class="fas fa-phone"></i> ' + H.e(phone) : '') + (cemail ? ' &middot; <i class="fas fa-envelope"></i> ' + H.e(cemail) : '') + '</p>';
        html += '<div class="report-meta"><span><i class="far fa-calendar"></i> Dicetak: ' + H.tglwaktu(new Date().toISOString()) + '</span><span><i class="fas fa-filter"></i> ' + H.e(filterDesc) + '</span></div></div>';
        // summary
        html += '<div class="report-section"><h4><i class="fas fa-chart-line"></i> Ringkasan</h4><div class="print-cards"><div class="print-card"><div class="label">Total Aset</div><div class="value">' + summary.total + '</div></div><div class="print-card"><div class="label">Tersedia</div><div class="value text-success">' + summary.tersedia + '</div></div><div class="print-card"><div class="label">Dipinjam</div><div class="value text-warning">' + summary.dipinjam + '</div></div><div class="print-card"><div class="label">Rusak</div><div class="value text-danger">' + summary.rusak + '</div></div>' + (H.priceVisible() ? '<div class="print-card"><div class="label">Total Nilai</div><div class="value">' + H.rp(summary.nilai_total) + '</div></div>' : '') + '</div><table class="report-table"><thead><tr><th>Status</th><th class="text-center">Total</th>' + (H.priceVisible() ? '<th class="text-right">Nilai</th>' : '') + '</tr></thead><tbody><tr><td>Tersedia</td><td class="text-center">' + summary.tersedia + '</td>' + (H.priceVisible() ? '<td class="text-right">' + H.rp(summary.nilai_tersedia) + '</td>' : '') + '</tr><tr><td>Dipinjam</td><td class="text-center">' + summary.dipinjam + '</td>' + (H.priceVisible() ? '<td class="text-right">' + H.rp(summary.nilai_dipinjam) + '</td>' : '') + '</tr><tr><td>Rusak</td><td class="text-center">' + summary.rusak + '</td>' + (H.priceVisible() ? '<td class="text-right">' + H.rp(summary.nilai_rusak) + '</td>' : '') + '</tr></tbody><tfoot><tr><td>Total</td><td class="text-center">' + summary.total + '</td>' + (H.priceVisible() ? '<td class="text-right">' + H.rp(summary.nilai_total) + '</td>' : '') + '</tr></tfoot></table></div>';
        // by category
        html += '<div class="report-section"><h4><i class="fas fa-tags"></i> Per Kategori</h4><table class="report-table"><thead><tr><th>#</th><th>Kategori</th><th class="text-center">Tersedia</th><th class="text-center">Dipinjam</th><th class="text-center">Rusak</th><th class="text-center">Total</th>' + (H.priceVisible() ? '<th class="text-right">Nilai</th>' : '') + '</tr></thead><tbody>';
        byCategory.forEach((c, i) => { html += '<tr><td>' + (i + 1) + '</td><td>' + H.e(c.category_name) + '</td><td class="text-center">' + c.tersedia + '</td><td class="text-center">' + c.dipinjam + '</td><td class="text-center">' + c.rusak + '</td><td class="text-center">' + c.total + '</td>' + (H.priceVisible() ? '<td class="text-right">' + H.rp(c.nilai) + '</td>' : '') + '</tr>'; });
        html += '</tbody></table></div>';
        // by location
        html += '<div class="report-section"><h4><i class="fas fa-map-marker-alt"></i> Per Lokasi</h4><table class="report-table"><thead><tr><th>#</th><th>Lokasi</th><th class="text-center">Tersedia</th><th class="text-center">Dipinjam</th><th class="text-center">Rusak</th><th class="text-center">Total</th>' + (H.priceVisible() ? '<th class="text-right">Nilai</th>' : '') + '</tr></thead><tbody>';
        byLocation.forEach((l, i) => { html += '<tr><td>' + (i + 1) + '</td><td>' + H.e(l.location) + '</td><td class="text-center">' + l.tersedia + '</td><td class="text-center">' + l.dipinjam + '</td><td class="text-center">' + l.rusak + '</td><td class="text-center">' + l.total + '</td>' + (H.priceVisible() ? '<td class="text-right">' + H.rp(l.nilai) + '</td>' : '') + '</tr>'; });
        html += '</tbody></table></div>';
        // detail
        let totalNilai = 0;
        html += '<div class="report-section"><h4><i class="fas fa-list"></i> Daftar Detail Aset (' + assets.length + ')</h4><table class="report-table"><thead><tr><th width="40">Foto</th><th>Kode</th><th>Nama</th><th>Kategori</th><th>Lokasi</th><th>Tgl. Beli</th><th>Status</th>' + (H.priceVisible() ? '<th class="text-right">Harga</th>' : '') + '</tr></thead><tbody>';
        assets.forEach(a => { totalNilai += Number(a.price || 0); html += '<tr><td>' + H.assetPhotoImg(a.photo, 30) + '</td><td>' + H.e(a.asset_code) + '</td><td>' + H.e(a.name) + '<br><small class="text-muted">' + H.e(a.brand_spec || '') + '</small></td><td>' + H.e(a.category_name) + '</td><td>' + H.e(a.location || '-') + '</td><td>' + H.tgl(a.purchase_date) + '</td><td>' + (a.status.charAt(0).toUpperCase() + a.status.slice(1)) + '</td>' + (H.priceVisible() ? '<td class="text-right">' + H.rp(a.price) + '</td>' : '') + '</tr>'; });
        html += '</tbody>' + (H.priceVisible() ? '<tfoot><tr><td colspan="7">Total Nilai</td><td class="text-right">' + H.rp(totalNilai) + '</td></tr></tfoot>' : '') + '</table></div>';
        // signature
        const monthsID = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        const u = Auth.user();
        html += '<div class="tanda-tangan"><div class="tt-label">' + new Date().getDate() + ' ' + monthsID[new Date().getMonth()] + ' ' + new Date().getFullYear() + '</div><div class="tt-label mt-2">Mengetahui,</div><div class="tt-name">' + H.e(u ? u.name : '') + '</div></div><div class="report-footer"><span>AssetManager v1.0.0 (HTML)</span><span>Database: LocalStorage</span></div>';
        Views.print(html);
    };

    global.Pages = P;
})(window);
