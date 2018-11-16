$ = jQuery;

$(document).ready(function() {
	
	$('#nodeDistance').change(function() {
		var distance = parseInt($( this ).val());
    var options = {
  	    physics: {
    	    enabled: true,
  	      repulsion: {
  	        centralGravity: 1,
  	        nodeDistance	: distance
  	      },
  	      minVelocity: .3,
  	      maxVelocity : 50,
  	      solver: 'repulsion',
  	      stabilization: {
  	        enabled: true,
  	        iterations: 1000,
  	        updateInterval: 100,
  	        onlyDynamicEdges: true,
  	        fit: true
  	      },
  	      timestep: 1.5,
  	      adaptiveTimestep: false      
  	    },
  	};
		network.setOptions(options);
		network.once("stabilized", function() {
      network.fit({
        animation: {
          duration: 1000,
          easingFunction: "linear"
        }
      });
    });
	});
});