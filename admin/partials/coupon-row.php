<?php
if (!isset($coupon)) return;
?>
<tr data-id="<?php echo $coupon->id; ?>">
    <td><?php echo esc_html($coupon->code); ?></td>
    <td><?php echo esc_html($coupon->type); ?></td>
    <td>
      <?php if ($coupon->type === 'fixed') {
        echo intval($coupon->discount) . ' DA';
      } else {
        echo intval($coupon->discount) . '%';
      } ?>
    </td>
    <td><?php echo esc_html($coupon->usage_count ?? 0) . ' / ' . esc_html($coupon->usage_limit); ?></td>
    <td><?php echo esc_html($coupon->valid_from); ?></td>
    <td><?php echo esc_html($coupon->valid_to); ?></td>
    <td>
        <a href="#" class="ib-btn-edit" title="Éditer" data-id="<?php echo $coupon->id; ?>"><span class="dashicons dashicons-edit"></span></a>
        <a href="#" class="ib-btn-delete" title="Supprimer" data-id="<?php echo $coupon->id; ?>"><span class="dashicons dashicons-trash"></span></a>
    </td>
</tr> 