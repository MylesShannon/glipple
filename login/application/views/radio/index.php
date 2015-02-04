<div class="content">
    <h1>Radio</h1>

    <!-- echo out the system feedback (error and success messages) -->
    <?php $this->renderFeedbackMessages(); ?>

	<!-- HTML5 player -->
    <p>
        <audio controls>
			<source src="http://glipple.com:8000/radio" type="audio/mpeg">
		Your browser does not support the audio element.
		</audio>
    </p>
</div>
