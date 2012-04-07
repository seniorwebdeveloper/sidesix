/**
 * Adobe Edge: symbol definitions
 */
(function($, Edge, compId){
var fonts = {
   "Bookman":"<script type=\"text/javascript\" src=\"http://use.typekit.com/akl7rhp.js\"></script><script type=\"text/javascript\">try{Typekit.load();}catch(e){}</script>"
};
var symbols = {
"stage": {
   version: "0.1.4",
   baseState: "Base State",
   initialState: "Base State",
   gpuAccelerate: true,
   content: {
      dom: [
        {
            id:'Text',
            type:'text',
            rect:[184,98,265,77],
            text:"Simplicity",
            font:["Bookman",60,"rgba(0,0,0,1)","normal","none",""],
            transform:[[-184.00001,-97.99999],[0,0],[0],[1,1]]
        },
        {
            id:'Text2',
            type:'text',
            rect:[113,146,226.90234375,51.44921875],
            text:"Superiority",
            align:"auto",
            font:["Bookman",60,"rgba(0,0,0,1)","normal","none","normal"],
            transform:[[374.00001,-159],[0,0],[0],[1,1]]
        },
        {
            id:'Text3',
            type:'text',
            rect:[220,149,265,77],
            text:"Side Six",
            align:"auto",
            font:["Bookman",60,"rgba(0,0,0,1)","normal","none","normal"],
            transform:[[-220.00002,63.46092],[0,0],[0],[1,1]]
        }],
      symbolInstances: [

      ]
   },
   states: {
      "Base State": {
         "${_Text3}": [
            ["transform", "translateX", '-220.00002px'],
            ["transform", "translateY", '63.46092px']
         ],
         "${_Text2}": [
            ["transform", "translateX", '374.00001px'],
            ["transform", "translateY", '-159px']
         ],
         "${_Text}": [
            ["transform", "translateX", '-184.00001px'],
            ["style", "width", '265px'],
            ["style", "height", '77px'],
            ["style", "font-family", 'Bookman'],
            ["transform", "translateY", '-97.99999px'],
            ["style", "font-size", '60px']
         ],
         "${_stage}": [
            ["color", "background-color", 'rgba(255,255,255,0)'],
            ["style", "width", '600px'],
            ["style", "height", '270px'],
            ["style", "overflow", 'hidden']
         ]
      }
   },
   timelines: {
      "Default Timeline": {
         fromState: "Base State",
         toState: "",
         duration: 0,
         autoPlay: true,
         labels: {

         },
         timeline: [
         ]
      }
   }
}
};

Edge.registerCompositionDefn(compId, symbols, fonts);

/**
 * Adobe Edge DOM Ready Event Handler
 */
$(window).ready(function() {
     Edge.launchComposition(compId);
});
})(jQuery, AdobeEdge, "EDGE-177217059");
