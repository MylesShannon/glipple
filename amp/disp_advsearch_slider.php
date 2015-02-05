<script>
$(function() {
	// TEMPO/BPM (0-500):
		$( "#slider-range-tempo" ).slider({
			range: true,
			min: 1,
			max: 500,
			values: [ 
			<?php if (isset($_SESSION['tempo_min'])) { echo $_SESSION['tempo_min']; } else { echo '80'; } ?>, 
			<?php if (isset($_SESSION['tempo_max'])) { echo $_SESSION['tempo_max']; } else { echo '130'; } ?>
			],
			step: 1,
			slide: function( event, ui ) {
				$( "#tempo" ).val( ui.values[ 0 ] + " - " + ui.values[ 1 ] );
			}
		});
		$( "#tempo" ).val( $( "#slider-range-tempo" ).slider( "values", 0 ) +
			" - " + $( "#slider-range-tempo" ).slider( "values", 1 ) );
	
	// DANCEABILITY (0-1):		
		$( "#slider-range-danceability" ).slider({
			range: true,
			min: 0.1,
			max: 1,
			values: [ 
			<?php if (isset($_SESSION['danceability_min'])) { echo $_SESSION['danceability_min']; } else { echo '0.4'; } ?>, 
			<?php if (isset($_SESSION['danceability_max'])) { echo $_SESSION['danceability_max']; } else { echo '0.7'; } ?>
			],
			step: 0.01,
			slide: function( event, ui ) {
				$( "#danceability" ).val( ui.values[ 0 ] + " - " + ui.values[ 1 ] );
			}
		});
		$( "#danceability" ).val( $( "#slider-range-danceability" ).slider( "values", 0 ) +
			" - " + $( "#slider-range-danceability" ).slider( "values", 1 ) );

	// ENERGY (0-1):		
		$( "#slider-range-energy" ).slider({
			range: true,
			min: 0.1,
			max: 1,
			values: [ 
			<?php if (isset($_SESSION['energy_min'])) { echo $_SESSION['energy_min']; } else { echo '0.4'; } ?>, 
			<?php if (isset($_SESSION['energy_max'])) { echo $_SESSION['energy_max']; } else { echo '0.7'; } ?>
			],
			step: 0.01,
			slide: function( event, ui ) {
				$( "#energy" ).val( ui.values[ 0 ] + " - " + ui.values[ 1 ] );
			}
		});
		$( "#energy" ).val( $( "#slider-range-energy" ).slider( "values", 0 ) +
			" - " + $( "#slider-range-energy" ).slider( "values", 1 ) );
					
	// LOUDNESS (-100 - 100):		
		$( "#slider-range-loudness" ).slider({
			range: true,
			min: -10,
			max: 10,
			values: [ 
			<?php if (isset($_SESSION['loudness_min'])) { echo $_SESSION['loudness_min']; } else { echo '-5'; } ?>, 
			<?php if (isset($_SESSION['loudness_max'])) { echo $_SESSION['loudness_max']; } else { echo '5'; } ?>
			],
			step: 0.01,
			slide: function( event, ui ) {
				$( "#loudness" ).val( ui.values[ 0 ] + " - " + ui.values[ 1 ] );
			}
		});
		$( "#loudness" ).val( $( "#slider-range-loudness" ).slider( "values", 0 ) +
			" - " + $( "#slider-range-loudness" ).slider( "values", 1 ) );

	// KEY (0-11):		
		$( "#slider-range-key" ).slider({
			range: true,
			min: 0,
			max: 11,
			values: [ 
			<?php if (isset($_SESSION['key_min'])) { echo $_SESSION['key_min']; } else { echo '1'; } ?>, 
			<?php if (isset($_SESSION['key_max'])) { echo $_SESSION['key_max']; } else { echo '10'; } ?>
			],
			step: 1,
			slide: function( event, ui ) {
				$( "#key" ).val( ui.values[ 0 ] + " - " + ui.values[ 1 ] );
			}
		});
		$( "#key" ).val( $( "#slider-range-key" ).slider( "values", 0 ) +
			" - " + $( "#slider-range-key" ).slider( "values", 1 ) );

	// TIME SIGNATURE (3-7):		
		$( "#slider-range-time_signature" ).slider({
			range: true,
			min: 3,
			max: 7,
			values: [ 
			<?php if (isset($_SESSION['time_signature_min'])) { echo $_SESSION['time_signature_min']; } else { echo '4'; } ?>, 
			<?php if (isset($_SESSION['time_signature_max'])) { echo $_SESSION['time_signature_max']; } else { echo '6'; } ?>
			],
			step: 1,
			slide: function( event, ui ) {
				$( "#time_signature" ).val( ui.values[ 0 ] + " - " + ui.values[ 1 ] );
			}
		});
		$( "#time_signature" ).val( $( "#slider-range-time_signature" ).slider( "values", 0 ) +
			" - " + $( "#slider-range-time_signature" ).slider( "values", 1 ) );
			
	// YEAR RANGE (1910-2013):		
		$( "#slider-range-year_range" ).slider({
			range: true,
			min: 1910,
			max: <?php echo date('Y'); ?>,
			values: [ 
			<?php if (isset($_SESSION['year_range_min'])) { echo $_SESSION['year_range_min']; } else { echo '1940'; } ?>, 
			<?php if (isset($_SESSION['year_range_max'])) { echo $_SESSION['year_range_max']; } else { echo date('Y'); } ?> 
			],
			step: 1,
			slide: function( event, ui ) {
				$( "#year_range" ).val( ui.values[ 0 ] + " - " + ui.values[ 1 ] );
			}
		});
		$( "#year_range" ).val( $( "#slider-range-year_range" ).slider( "values", 0 ) +
			" - " + $( "#slider-range-year_range" ).slider( "values", 1 ) );
			
	// LIVENESS (0-1) - new in AmpJuke 0.8.8:		
	$( "#slider-range-liveness" ).slider({
		range: true,
		min: 0.1,
		max: 1,
		values: [ 
		<?php if (isset($_SESSION['liveness_min'])) { echo $_SESSION['liveness_min']; } else { echo '0.4'; } ?>, 
		<?php if (isset($_SESSION['liveness_max'])) { echo $_SESSION['liveness_max']; } else { echo '0.7'; } ?>
		],
		step: 0.01,
		slide: function( event, ui ) {
			$( "#liveness" ).val( ui.values[ 0 ] + " - " + ui.values[ 1 ] );
		}
	});
	$( "#liveness" ).val( $( "#slider-range-liveness" ).slider( "values", 0 ) +
		" - " + $( "#slider-range-liveness" ).slider( "values", 1 ) );

	// Speechiness (0-1) - new in AmpJuke 0.8.8:		
	$( "#slider-range-speechiness" ).slider({
		range: true,
		min: 0.1,
		max: 1,
		values: [ 
		<?php if (isset($_SESSION['speechiness_min'])) { echo $_SESSION['speechiness_min']; } else { echo '0.4'; } ?>, 
		<?php if (isset($_SESSION['speechiness_max'])) { echo $_SESSION['speechiness_max']; } else { echo '0.7'; } ?>
		],
		step: 0.01,
		slide: function( event, ui ) {
			$( "#speechiness" ).val( ui.values[ 0 ] + " - " + ui.values[ 1 ] );
		}
	});
	$( "#speechiness" ).val( $( "#slider-range-speechiness" ).slider( "values", 0 ) +
		" - " + $( "#slider-range-speechiness" ).slider( "values", 1 ) );

	// Acousticness (0-1) - new in AmpJuke 0.8.8:		
	$( "#slider-range-acousticness" ).slider({
		range: true,
		min: 0.1,
		max: 1,
		values: [ 
		<?php if (isset($_SESSION['acousticness_min'])) { echo $_SESSION['acousticness_min']; } else { echo '0.4'; } ?>, 
		<?php if (isset($_SESSION['acousticness_max'])) { echo $_SESSION['acousticness_max']; } else { echo '0.7'; } ?>
		],
		step: 0.01,
		slide: function( event, ui ) {
			$( "#acousticness" ).val( ui.values[ 0 ] + " - " + ui.values[ 1 ] );
		}
	});
	$( "#acousticness" ).val( $( "#slider-range-acousticness" ).slider( "values", 0 ) +
		" - " + $( "#slider-range-acousticness" ).slider( "values", 1 ) );

	// Valence (0-1) - new in AmpJuke 0.8.8:		
	$( "#slider-range-valence" ).slider({
		range: true,
		min: 0.1,
		max: 1,
		values: [ 
		<?php if (isset($_SESSION['valence_min'])) { echo $_SESSION['valence_min']; } else { echo '0.4'; } ?>, 
		<?php if (isset($_SESSION['valence_max'])) { echo $_SESSION['valence_max']; } else { echo '0.7'; } ?>
		],
		step: 0.01,
		slide: function( event, ui ) {
			$( "#valence" ).val( ui.values[ 0 ] + " - " + ui.values[ 1 ] );
		}
	});
	$( "#valence" ).val( $( "#slider-range-valence" ).slider( "values", 0 ) +
		" - " + $( "#slider-range-valence" ).slider( "values", 1 ) );
		
});
</script>
