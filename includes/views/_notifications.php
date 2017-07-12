<?php foreach($this->notifications as $message): ?>
	<div class='notice-<?php echo $message['type']; ?> notice is-dismissible experitus-message-box'>
		<p>
			<?php echo $message['type'] == 'error' ? '<strong>Error! </strong>' : ''; ?>
			<?php echo $message['text']; ?>
		</p>
	</div>
<?php endforeach; ?>