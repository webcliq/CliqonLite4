<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>Converting LatLng to Pixel Coordinates</title>
    <style type="text/css">
      @import url('../admin/style/admin.css');
      @import url('../admin/style/admin-ui.css');
      #map {
        width: 498px;
        height: 410px;
      }
      
      #latlng-control {
        background: #ffc;
        border: 1px solid #676767;
        font-family: arial, helvetica, sans-serif;
        font-size: 0.7em;
        padding: 2px 4px;
        position: absolute;
      }
    </style>
    <script type="text/javascript" src="../js/php.js"></script>
    <script type="text/javascript" src="../js/jquery.js"></script>
    <script type="text/javascript" src="../js/jquery-ui.js"></script>
    <script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>

    <script type="text/javascript">
      /**
       * LatLngControl class displays the LatLng and pixel coordinates
       * underneath the mouse within a container anchored to it.
       * @param {google.maps.Map} map Map to add custom control to.
       */
      function LatLngControl(map) {
        /*Offset the control container from the mouse by this amount. */
        this.ANCHOR_OFFSET_ = new google.maps.Point(8, 8);
        
        /* Pointer to the HTML container. */
        this.node_ = this.createHtmlNode_();
        
        // Add control to the map. Position is irrelevant.
        map.controls[google.maps.ControlPosition.TOP].push(this.node_);
        
        // Bind this OverlayView to the map so we can access MapCanvasProjection
        // to convert LatLng to Point coordinates.
        this.setMap(map);
        
        // Register an MVC property to indicate whether this custom control
        // is visible or hidden. Initially hide control until mouse is over map.
        this.set('visible', false);
      }
      
      // Extend OverlayView so we can access MapCanvasProjection.
      LatLngControl.prototype = new google.maps.OverlayView();
      LatLngControl.prototype.draw = function() {};
      
      /**
       * @private
       * Helper function creates the HTML node which is the control container.
       * @return {HTMLDivElement}
       */
      LatLngControl.prototype.createHtmlNode_ = function() {
        var divNode = document.createElement('div');
        divNode.id = 'latlng-control';
        divNode.index = 100;
        return divNode;
      };
      
      /**
       * MVC property's state change handler function to show/hide the
       * control container.
       */
      LatLngControl.prototype.visible_changed = function() {
        this.node_.style.display = this.get('visible') ? '' : 'none';
      };
      
      /**
       * Specified LatLng value is used to calculate pixel coordinates and
       * update the control display. Container is also repositioned.
       * @param {google.maps.LatLng} latLng Position to display
       */
      LatLngControl.prototype.updatePosition = function(latLng) {
        var projection = this.getProjection();
        var point = projection.fromLatLngToContainerPixel(latLng);
        
        // Update control position to be anchored next to mouse position.
        this.node_.style.left = point.x + this.ANCHOR_OFFSET_.x + 'px';
        this.node_.style.top = point.y + this.ANCHOR_OFFSET_.y + 'px';
        
        // Update control to display latlng and coordinates.
        this.node_.innerHTML = [ latLng.toUrlValue(4) ].join('');
      };
      
      /**
       * Called on the intiial pageload.
       */

      var mapOptions = {

          zoom: 11,
          center: new google.maps.LatLng(39.5750, 2.6537),
          mapTypeControl: true,
          mapTypeControlOptions: {
            mapTypeIds: [google.maps.MapTypeId.HYBRID,google.maps.MapTypeId.ROADMAP],
            style: google.maps.MapTypeControlStyle.DROPDOWN_MENU
          },
          zoomControl: true,
          zoomControlOptions: {
            style: google.maps.ZoomControlStyle.SMALL
          },
          mapTypeId: google.maps.MapTypeId.ROADMAP

      }

      function init() {
        var map = new google.maps.Map(document.getElementById('map'), mapOptions);
        
        // Create new control to display latlng and coordinates under mouse.
        var latLngControl = new LatLngControl(map);
        
        // Register event listeners
        google.maps.event.addListener(map, 'mouseover', function(mEvent) {
          latLngControl.set('visible', true);
        });
        google.maps.event.addListener(map, 'mouseout', function(mEvent) {
          latLngControl.set('visible', false);
        });
        google.maps.event.addListener(map, 'mousemove', function(mEvent) {
          latLngControl.updatePosition(mEvent.latLng);
        });
        google.maps.event.addListener(map, 'click', function(mEvent) {
          latLngControl.updatePosition(mEvent.latLng);
          // document.getElementById('raw').innerHTML = mEvent.latLng.toUrlValue(4);
          var mapc = explode(",", mEvent.latLng.toUrlValue(4));
          $('#mapx').html(mapc[0]);
          $('#mapy').html(mapc[1]);
        });
      }

      // Register an event listener to fire when the page finishes loading.
      google.maps.event.addDomListener(window, 'load', init);

      $(document).ready(function() {
        $('.btn').button();
        $('#transferbutton').click(function() {

            var mapx = $('#mapx').html() ;
            var mapy = $('#mapy').html();
            if(mapx != "") {
                // alert('<?php echo $_REQUEST['mapx'] ?> >> '+mapx+', <?php echo $_REQUEST['mapy'] ?> >> '+mapy);
                $('#<?php echo $_REQUEST['mapx'] ?>', window.parent.document).val(mapx);
                $('#<?php echo $_REQUEST['mapy'] ?>', window.parent.document).val(mapy);            
            }
        })
      })
    </script>
  </head>
  <body style="padding:0px; margin:0px;">
    <div id="map" style="border: 1px solid #ccc;"></div>
    <div id="coords" style="padding:3px; padding-left:20px; font-weight:bold; background-color: #fff;" >
        <span id="raw"></span> X: <span id="mapx" style="width:80px;"></span> Y: <span id="mapy"  style="width:80px;"></span><input type="button" value="  >>  " class="btn transferbutton" id="transferbutton" />
    </div>
  </body>
</html>