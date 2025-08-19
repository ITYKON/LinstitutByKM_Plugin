<?php
// $selected_date doit être défini avant l'include
$start_hour = 9;
$end_hour = 18;
$slot_minutes = 15;
$employees = IB_Employees::get_all();
$bookings = IB_Bookings::get_all();
$employee_colors = [];
$employee_palette = ['#4f8cff','#00c48c','#ffb300','#ff4f64','#7c3aed','#ff6f00','#00bcd4','#8bc34a','#e67e22','#e84393','#00b894','#636e72','#fdcb6e','#0984e3','#d35400','#6c5ce7'];
foreach ($employees as $i => $emp) {
    $employee_colors[$emp->id] = $employee_palette[$i % count($employee_palette)];
}
$slots = [];
for ($h = $start_hour; $h < $end_hour; $h++) {
    for ($m = 0; $m < 60; $m += $slot_minutes) {
        $slots[] = sprintf('%02d:%02d', $h, $m);
    }
}
if (!function_exists('get_slot_index')) {
function get_slot_index($time, $slots) {
    foreach ($slots as $i => $slot) {
        if ($slot == $time) return $i + 2;
    }
    [$h, $m] = explode(':', $time);
    $time_minutes = $h * 60 + $m;
    foreach ($slots as $i => $slot) {
        [$sh, $sm] = explode(':', $slot);
        $slot_minutes = $sh * 60 + $sm;
        if ($slot_minutes > $time_minutes) return $i + 2;
    }
    return count($slots) + 1;
}}
if (!function_exists('get_slot_span')) {
function get_slot_span($start, $duration, $slots) {
    $start_i = get_slot_index($start, $slots);
    $start_minutes = (int)substr($start,0,2)*60 + (int)substr($start,3,2);
    $end_minutes = $start_minutes + $duration;
    $end_h = floor($end_minutes/60);
    $end_m = $end_minutes%60;
    $end = sprintf('%02d:%02d', $end_h, $end_m);
    $end_i = get_slot_index($end, $slots);
    return max(1, $end_i - $start_i);
}}
$prev_date = date('Y-m-d', strtotime($selected_date . ' -1 day'));
$next_date = date('Y-m-d', strtotime($selected_date . ' +1 day'));
setlocale(LC_TIME, 'fr_FR.UTF-8', 'fr_FR', 'fr', 'fra');
$date_fr = strftime('%A %d %B %Y', strtotime($selected_date));
?>
<div class="ib-matrix-header-bar" style="display:flex;align-items:center;justify-content:center;margin-bottom:1em;gap:1.5em;">
  <button class="ib-matrix-arrow" data-date="<?php echo $prev_date; ?>">&#8592;</button>
  <span class="ib-matrix-date" style="font-size:1.25em;font-weight:700;color:#e9aebc;letter-spacing:0.5px;text-transform:capitalize;">
    <?php echo ucfirst($date_fr); ?>
  </span>
  <button class="ib-matrix-arrow" data-date="<?php echo $next_date; ?>">&#8594;</button>
</div>
<div class="ib-matrix-calendar">
  <div class="ib-matrix-grid"
    style="display:grid;
      grid-template-columns: 70px repeat(<?php echo count($employees); ?>, 1fr);
      grid-template-rows: 40px repeat(<?php echo count($slots); ?>, 1fr);">
    <!-- En-têtes -->
    <div class="ib-matrix-header"></div>
    <?php foreach ($employees as $emp): ?>
      <div class="ib-matrix-header" style="text-align:center;">
        <?php echo esc_html($emp->name); ?>
      </div>
    <?php endforeach; ?>
    <!-- Lignes horaires -->
    <?php foreach ($slots as $i => $slot): ?>
      <div class="ib-matrix-time" style="grid-row:<?php echo $i+2; ?>;grid-column:1;">
        <span class="<?php echo (substr($slot,3,2)==='00') ? 'ib-matrix-hour' : 'ib-matrix-quarter'; ?>">
          <?php echo htmlspecialchars($slot); ?>
        </span>
      </div>
      <?php foreach ($employees as $j => $emp): ?>
        <div class="ib-matrix-cell" style="grid-row:<?php echo $i+2; ?>;grid-column:<?php echo $j+2; ?>;"></div>
      <?php endforeach; ?>
    <?php endforeach; ?>
    <!-- Blocs de réservation -->
    <?php foreach ($bookings as $b): ?>
      <?php
        if ($b->date != $selected_date) continue;
        $emp_index = array_search($b->employee_id, array_column($employees, 'id'));
        if ($emp_index === false) continue;
        $col = 2 + $emp_index;
        $start_dt = (strlen($b->start_time) > 5) ? substr($b->start_time,11,5) : substr($b->start_time,0,5);
        $service = IB_Services::get_by_id($b->service_id);
        $duration = $service && isset($service->duration) ? intval($service->duration) : 30;
        $row = get_slot_index($start_dt, $slots);
        $span = get_slot_span($start_dt, $duration, $slots);
        $color = $employee_colors[$b->employee_id] ?? '#e9aebc';
        $start_minutes = (int)substr($start_dt,0,2)*60 + (int)substr($start_dt,3,2);
        $end_minutes = $start_minutes + $duration;
        $end_h = floor($end_minutes/60);
        $end_m = $end_minutes%60;
        $end_dt = sprintf('%02d:%02d', $end_h, $end_m);
        if ($start_dt < $slots[0] || $start_dt >= $slots[count($slots)-1]) continue;
        if ($end_dt > $slots[count($slots)-1]) continue;
      ?>
      <div class="ib-matrix-booking"
        style="
          grid-column:<?php echo $col; ?>;
          grid-row:<?php echo $row; ?>/span <?php echo $span; ?>;
          --emp-color: <?php echo $color; ?>;
          --emp-bg: <?php echo $color; ?>22;
          background: var(--emp-bg);
          border-left:4px solid var(--emp-color);
          ">
        <div class="ib-matrix-booking-title" style="color:<?php echo $color; ?>;">
          <strong><?php echo esc_html($service ? $service->name : ''); ?></strong>
        </div>
        <div class="ib-matrix-booking-client"><?php echo esc_html($b->client_name); ?></div>
        <div class="ib-matrix-booking-time"><?php echo htmlspecialchars($start_dt).' - '.$end_dt; ?></div>
      </div>
    <?php endforeach; ?>
  </div>
</div> 