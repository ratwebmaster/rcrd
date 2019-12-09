<?php if(!empty($staffMembers)) { ?>
	<ul class="staff-member-list">
		<?php foreach($staffMembers as $staffMember) { ?>
			<li class="staff-member"><a href="<?php echo get_permalink($staffMember->ID); ?>"><?php echo $staffMember->post_title; ?></a></li>
		<?php } ?>
	</ul>
<?php } ?>