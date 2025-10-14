<?php
/* Toggle file */
?>
<div class="wc-donation-type-toggle">
	<div class="toggle-item">
		<input type="radio" name="is_recurring" id="" value="no" class="toggle-item-input" checked onchange="WCCheckoutDonation.donationTypeChange(this)">
		<div class="toggle-item-label">One Time</div>
		<div class="item-check"></div>
	</div>
	<div class="toggle-item">
		<input type="radio" name="is_recurring" id="" value="yes" class="toggle-item-input" onchange="WCCheckoutDonation.donationTypeChange(this)">
		<div class="toggle-item-label">Repeating</div>
		<div class="toggle-item-icon-shadow"></div>
		<div class="toggle-item-icon-heart"></div>
		<div class="item-check"></div>
	</div>
</div>