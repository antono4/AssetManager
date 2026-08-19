<?php /** Form Buat/Edit Jadwal Patching */
$s = $schedule;
$isEdit = $action === 'edit';
$cur = $current;
// Default nilai
$quarter = (int)($s['quarter'] ?? $cur['quarter']);
$year = (int)($s['year'] ?? $cur['year']);
$periodValue = sprintf('%04d-01-01', $year > 0 ? $year : (int)date('Y'));
$quarterLabels = [
    1 => 'Q1 (Jan-Mar)',
    2 => 'Q2 (Apr-Jun)',
    3 => 'Q3 (Jul-Sep)',
    4 => 'Q4 (Okt-Des)',
];
?>
<div class="card card-primary">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-<?= $isEdit?'edit':'plus' ?> mr-1"></i> <?= $isEdit ? 'Edit Jadwal Patching' : 'Buat Jadwal Patching Baru' ?></h3></div>
    <form method="post" action="<?= $isEdit ? url('patching/' . $s['id']) : url('patching') ?>">
        <div class="card-body">
            <div class="form-group">
                <label>Nama Jadwal <span class="text-danger">*</span></label>
                <input type="text" name="name" id="sched-name" class="form-control" required value="<?= e($s['name'] ?? '') ?>" placeholder="Mis: Patching Q3 2026">
                <small class="text-muted">Akan terisi otomatis dari kuartal & tahun bila dikosongkan.</small>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Kuartal <span class="text-danger">*</span></label>
                        <select name="quarter" id="sched-quarter" class="form-control" required>
                            <?php foreach ($quarterLabels as $q => $label): ?>
                                <option value="<?= $q ?>" <?= $quarter===$q?'selected':'' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Periode 3 bulan: Q1=Jan-Mar, Q2=Apr-Jun, dst.</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Periode / Tahun Patching <span class="text-danger">*</span></label>
                        <input type="date" name="period" id="sched-period" class="form-control" required value="<?= e($periodValue) ?>">
                        <small class="text-muted">Pilih tanggal di tahun patching; tahunnya yang dipakai untuk jadwal.</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Tanggal Mulai</label>
                        <input type="date" name="start_date" id="sched-start" class="form-control" value="<?= e($s['start_date'] ?? '') ?>">
                        <small class="text-muted">Kosongkan = awal kuartal.</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Batas Akhir (Due)</label>
                        <input type="date" name="due_date" id="sched-due" class="form-control" value="<?= e($s['due_date'] ?? '') ?>">
                        <small class="text-muted">Kosongkan = akhir kuartal.</small>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-control">
                    <option value="draft"     <?= ($s['status'] ?? 'draft')==='draft'?'selected':'' ?>>Draft</option>
                    <option value="ongoing"   <?= ($s['status'] ?? '')==='ongoing'?'selected':'' ?>>Berjalan</option>
                    <option value="completed" <?= ($s['status'] ?? '')==='completed'?'selected':'' ?>>Selesai</option>
                </select>
            </div>
            <div class="form-group">
                <label>Deskripsi</label>
                <textarea name="description" class="form-control" rows="2" placeholder="Catatan jadwal patching..."><?= e($s['description'] ?? '') ?></textarea>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
            <a href="<?= $isEdit ? url('patching/' . $s['id']) : url('patching') ?>" class="btn btn-default">Batal</a>
        </div>
    </form>
</div>

<script>
// Auto-generate nama & tanggal dari kuartal/tahun bila nama kosong
(function(){
    var q = document.getElementById('sched-quarter');
    var p = document.getElementById('sched-period');
    var name = document.getElementById('sched-name');
    var start = document.getElementById('sched-start');
    var due = document.getElementById('sched-due');
    function quarterDates(quarter, year){
        var startMonth = (quarter - 1) * 3 + 1;
        var sd = year + '-' + String(startMonth).padStart(2,'0') + '-01';
        var endMonth = startMonth + 2;
        var ed = new Date(year, endMonth, 0).toISOString().slice(0,10);
        return {start: sd, end: ed};
    }
    function periodYear(){
        var y = parseInt((p.value || '').substring(0, 4), 10);
        return y >= 1900 ? y : new Date().getFullYear();
    }
    function update(){
        var qq = parseInt(q.value, 10), yy = periodYear();
        if(!name.value || /^Patching Q[1-4] \d{4}$/.test(name.value)){
            name.value = 'Patching Q' + qq + ' ' + yy;
        }
        if(!start.value || !due.value){
            var d = quarterDates(qq, yy);
            if(!start.value) start.value = d.start;
            if(!due.value) due.value = d.end;
        }
    }
    q.addEventListener('change', update);
    p.addEventListener('change', update);
    p.addEventListener('input', update);
    update();
})();
</script>
