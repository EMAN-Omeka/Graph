<?php
echo head(array('bodyclass' => 'items show'));

$visdir = WEB_ROOT . '/plugins/Graph/javascripts/vis/'; ?>

<script type="text/javascript" src="<?php echo $visdir; ?>vis.js"></script>
    <link href="<?php echo $visdir; ?>vis.css" rel="stylesheet" type="text/css" />

    <style type="text/css">
        #mynetwork {
            width: 992px;
            height: 800px;
            padding:12px 16px;
            margin:10px;
            border: 1px solid lightgray;
        }
    </style>
  
<div id="visualization"></div>

<div id="mynetwork"></div>

<script type="text/javascript">
  // DOM element where the Timeline will be attached
  var container = document.getElementById('visualization');

  // Create a DataSet (allows two way data-binding)
  var items = new vis.DataSet([
    {id: 1, content: 'item 1', start: '2013-04-20'},
    {id: 2, content: 'item 2', start: '2013-04-14'},
    {id: 3, content: 'item 3', start: '2013-04-18'},
    {id: 4, content: 'item 4', start: '2013-04-16', end: '2013-04-19'},
    {id: 5, content: 'item 5', start: '2013-04-25'},
    {id: 6, content: 'item 6', start: '2013-04-27'}
  ]);

  // Configuration for the Timeline
  var options = {};

  // Create a Timeline
  var timeline = new vis.Timeline(container, items, options);

  var nodes = null;
  var edges = null;
  var network = null;

  function destroy() {
    if (network !== null) {
      network.destroy();
      network = null;
    }
  }

  function draw() {
    destroy();
    // create people.
    // value corresponds with the age of the person
    nodes = [
      {id: 1,  value: 2,  label: 'Algie' },
      {id: 2,  value: 31, label: 'Alston'},
      {id: 3,  value: 12, label: 'Barney'},
      {id: 4,  value: 16, label: 'Coley' },
      {id: 5,  value: 17, label: 'Grant' },
      {id: 6,  value: 15, label: 'Langdon'},
      {id: 7,  value: 6,  label: 'Lee'},
      {id: 8,  value: 5,  label: 'Merlin'},
      {id: 9,  value: 30, label: 'Mick'},
      {id: 10, value: 18, label: 'Tod'},
    ];

    // create connections between people
    // value corresponds with the amount of contact between two people
    edges = [
      {from: 2, to: 8, value: 3, title: '3 emails per week'},
      {from: 2, to: 9, value: 5, title: '5 emails per week'},
      {from: 2, to: 10,value: 1, title: '1 emails per week'},
      {from: 4, to: 6, value: 8, title: '8 emails per week'},
      {from: 5, to: 7, value: 2, title: '2 emails per week'},
      {from: 4, to: 5, value: 1, title: '1 emails per week'},
      {from: 9, to: 10,value: 2, title: '2 emails per week'},
      {from: 2, to: 3, value: 6, title: '6 emails per week'},
      {from: 3, to: 9, value: 4, title: '4 emails per week'},
      {from: 5, to: 3, value: 1, title: '1 emails per week'},
      {from: 2, to: 7, value: 4, title: '4 emails per week'}
    ];

    // Instantiate our network object.
    var container = document.getElementById('mynetwork');
    var data = {
      nodes: nodes,
      edges: edges
    };
    var options = {
      nodes: {
        shape: 'dot',
        scaling:{
          label: {
            min:8,
            max:20
          }
        }
      }
    };
    network = new vis.Network(container, data, options);
  }  

  function draw2() {
    destroy();

    var nodes = [];
    var edges = [];
    // randomly create some nodes and edges
    for (var i = 0; i < 19; i++) {
      nodes.push({id: i, label: String(i)});
    }
    edges.push({from: 0, to: 1});
    edges.push({from: 0, to: 6});
    edges.push({from: 0, to: 13});
    edges.push({from: 0, to: 11});
    edges.push({from: 1, to: 2});
    edges.push({from: 2, to: 3});
    edges.push({from: 2, to: 4});
    edges.push({from: 3, to: 5});
    edges.push({from: 1, to: 10});
    edges.push({from: 1, to: 7});
    edges.push({from: 2, to: 8});
    edges.push({from: 2, to: 9});
    edges.push({from: 3, to: 14});
    edges.push({from: 1, to: 12});
    edges.push({from: 16, to: 15});
    edges.push({from: 15, to: 17});
    edges.push({from: 18, to: 17});

    // create a network
    var container = document.getElementById('mynetwork');
    var data = {
      nodes: nodes,
      edges: edges
    };

    var options = {
      layout: {
        hierarchical: {
          sortMethod: 'directed'
        }
      },
      edges: {
        smooth: true,
        arrows: {to : true }
      }
    };
    network = new vis.Network(container, data, options);
  }  
  function draw3() {
    destroy();
    // create some nodes
    var nodes = [
      {id: 1, label: 'Node in\nthe center', shape: 'text', font:{strokeWidth:4}},
      {id: 2, label: 'Node\nwith\nmultiple\nlines', shape: 'circle'},
      {id: 3, label: 'This is a lot of text\nbut luckily we can spread\nover multiple lines', shape: 'database'},
      {id: 4, label: 'This is text\non multiple lines', shape: 'box'},
      {id: 5, label: 'Little text', shape: 'ellipse'}
    ];

    // create some edges
    var edges = [
      {from: 1, to: 2, color: 'red', width: 3, length: 200}, // individual length definition is possible
      {from: 1, to: 3, dashes:true, width: 1, length: 200},
      {from: 1, to: 4, width: 1, length: 200, label:'I\'m an edge!'},
      {from: 1, to: 5, arrows:'to', width: 3, length: 200, label:'arrows\nare cool'}
    ];

    // create a network
    var container = document.getElementById('mynetwork');
    var data = {
      nodes: nodes,
      edges: edges
    };
    var options = {};
    var network = new vis.Network(container, data, options);
  }  
function draw4() {
	destroy();
  var nodes = new vis.DataSet([
                               {id: 1, label: 'Node 1'},
                               {id: 2, label: 'Node 2'},
                               {id: 3, label: 'Node 3'},
                               {id: 4, label: 'Node 4'},
                               {id: 5, label: 'Node 5'},
                               {id: 6, label: 'Node 6'}
                             ]);

                             // create an array with edges
                             var edges = new vis.DataSet([
                               {from: 1, to: 3, dashes:true},
                               {from: 1, to: 2, dashes:[5,5]},
                               {from: 2, to: 4, dashes:[5,5,3,3]},
                               {from: 2, to: 5, dashes:[2,2,10,10]},
                               {from: 2, to: 6, dashes:false},
                             ]);

                             // create a network
                             var container = document.getElementById('mynetwork');
                             var data = {
                               nodes: nodes,
                               edges: edges
                             };
                             var options = {};
                             var network = new vis.Network(container, data, options);	
}
function draw5() {
	destroy();
  var nodes = [
               {id: 0, label: "0", group: 'source'},
               {id: 1, label: "1", group: 'icons'},
               {id: 2, label: "2", group: 'icons'},
               {id: 3, label: "3", group: 'icons'},
               {id: 4, label: "4", group: 'icons'},
               {id: 5, label: "5", group: 'icons'},
               {id: 6, label: "6", group: 'icons'},
               {id: 7, label: "7", group: 'icons'},
               {id: 8, label: "8", group: 'icons'},
               {id: 9, label: "9", group: 'icons'},
               {id: 10, label: "10", group: 'mints'},
               {id: 11, label: "11", group: 'mints'},
               {id: 12, label: "12", group: 'mints'},
               {id: 13, label: "13", group: 'mints'},
               {id: 14, label: "14", group: 'mints'},
               {id: 15, group: 'dotsWithLabel'},
               {id: 16, group: 'dotsWithLabel'},
               {id: 17, group: 'dotsWithLabel'},
               {id: 18, group: 'dotsWithLabel'},
               {id: 19, group: 'dotsWithLabel'},
               {id: 20, label: "diamonds", group: 'diamonds'},
               {id: 21, label: "diamonds", group: 'diamonds'},
               {id: 22, label: "diamonds", group: 'diamonds'},
               {id: 23, label: "diamonds", group: 'diamonds'},
           ];
           var edges = [
               {from: 1, to: 0},
               {from: 2, to: 0},
               {from: 4, to: 3},
               {from: 5, to: 4},
               {from: 4, to: 0},
               {from: 7, to: 6},
               {from: 8, to: 7},
               {from: 7, to: 0},
               {from: 10, to: 9},
               {from: 11, to: 10},
               {from: 10, to: 4},
               {from: 13, to: 12},
               {from: 14, to: 13},
               {from: 13, to: 0},
               {from: 16, to: 15},
               {from: 17, to: 15},
               {from: 15, to: 10},
               {from: 19, to: 18},
               {from: 20, to: 19},
               {from: 19, to: 4},
               {from: 22, to: 21},
               {from: 23, to: 22},
               {from: 23, to: 0},
           ]

           // create a network
           var container = document.getElementById('mynetwork');
           var data = {
               nodes: nodes,
               edges: edges
           };
           var options = {
               nodes: {
                   shape: 'dot',
                   size: 20,
                   font: {
                       size: 15,
                       color: '#000000'
                   },
                   borderWidth: 2
               },
               edges: {
                   width: 2
               },
               groups: {
                   diamonds: {
                       color: {background:'red',border:'white'},
                       shape: 'diamond'
                   },
                   dotsWithLabel: {
                       label: "I'm a dot!",
                       shape: 'dot',
                       color: 'cyan'
                   },
                   mints: {color:'rgb(0,255,140)'},
                   icons: {
                       shape: 'icon',
                       icon: {
                           face: 'FontAwesome',
                           code: '\uf0c0',
                           size: 50,
                           color: 'orange'
                       }
                   },
                   source: {
                       color:{border:'white'}
                   }
               }
           };
           var network = new vis.Network(container, data, options);
	
}
function draw6() {
	destroy();
    nodes = [
      {id: 1,  label: 'circle',  shape: 'circle' },
      {id: 2,  label: 'ellipse', shape: 'ellipse'},
      {id: 3,  label: 'database',shape: 'database'},
      {id: 4,  label: 'box',     shape: 'box'    },
      {id: 5,  label: 'diamond', shape: 'diamond'},
      {id: 6,  label: 'dot',     shape: 'dot'},
      {id: 7,  label: 'square',  shape: 'square'},
      {id: 8,  label: 'triangle',shape: 'triangle'},
      {id: 9,  label: 'triangleDown', shape: 'triangleDown'},
      {id: 10, label: 'text',    shape: 'text'},
      {id: 11, label: 'star',    shape: 'star'},
      {id: 21, font:{size:30},          label: 'big circle',  shape: 'circle' },
      {id: 22, font:{size:30},          label: 'big ellipse', shape: 'ellipse'},
      {id: 23, font:{size:30},          label: 'ellipse with a long label text', shape: 'ellipse'},
      {id: 24, font:{size:30},          label: 'big database',shape: 'database'},
      {id: 25, font:{size:30},          label: 'big box',     shape: 'box'    },
      {id: 26, font:{size:30}, size:40, label: 'big diamond', shape: 'diamond'},
      {id: 27, font:{size:30}, size:40, label: 'big dot',     shape: 'dot'},
      {id: 28, font:{size:30}, size:40, label: 'big square',  shape: 'square'},
      {id: 29, font:{size:30}, size:40, label: 'big triangle',shape: 'triangle'},
      {id: 30, font:{size:30}, size:40, label: 'big triangleDown', shape: 'triangleDown'},
      {id: 31, font:{size:30},          label: 'big text',    shape: 'text'},
      {id: 32, font:{size:30}, size:40, label: 'big star',    shape: 'star'}
    ];

    edges = [
    ];

    // create a network
    var container = document.getElementById('mynetwork');
    var data = {
      nodes: nodes,
      edges: edges
    };
    var options = {physics:{barnesHut:{gravitationalConstant:-4000}}};
    network = new vis.Network(container, data, options);
}
  // Click ouvre la page de l'item
  timeline.on("click", function (params) {
//     params.event = "[original event]";
//     console.log(params);
    var graphToLoad = params['item'];
    if (graphToLoad == 1) {draw()};
    if (graphToLoad == 2) {draw2()};
    if (graphToLoad == 3) {draw3()};
    if (graphToLoad == 4) {draw4()};
    if (graphToLoad == 5) {draw5()};
    if (graphToLoad == 6) {draw6()};
});

	draw();
</script>


<?php echo foot(); ?>
