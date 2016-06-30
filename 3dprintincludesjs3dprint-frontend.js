/**
 * @author Sergey Burkov, http://www.wp3dprinting.com
 * @copyright 2015
 */
p3d.bar_progress=0;
p3d.xhr1='';
p3d.xhr2='';
p3d.xhr3='';
p3d.filereader_supported=true;
p3d.file_selected=0;
p3d.aabb = new Array();
p3d.resize_scale = 1;
p3d.cookie_expire = parseInt(p3d.cookie_expire);
p3d.refresh_interval = "";
p3d.refresh_interval1 = "";
p3d.refresh_interval1_running = false;
p3d.uploading = false;

window.onload = function() {
	jQuery('#price-container span.amount').html('&nbsp;');
	window.p3d_canvas = document.getElementById('p3d-cv');
	jQuery("#p3d-file-loading").css({
		top: jQuery("#p3d-cv").position().top+jQuery("#p3d-cv").height()/2-jQuery("#p3d-file-loading").height()/2,
		left: jQuery("#p3d-cv").position().left + jQuery("#p3d-cv").width()/2-jQuery("#p3d-file-loading").width()/2
	}) ;
	jQuery("#canvas-stats").css({
		top: jQuery("#p3d-cv").position().top ,
		left: jQuery("#p3d-cv").position().left
	}) ;
	var logoTimerID = 0;

	window.p3d_viewer = new JSC3D.Viewer(window.p3d_canvas);
	p3d_viewer.setParameter('InitRotationX', p3d.angle_x);
	p3d_viewer.setParameter('InitRotationY', p3d.angle_y);
	p3d_viewer.setParameter('InitRotationZ', p3d.angle_z);
	p3d_viewer.setParameter('BackgroundColor1', p3d.background1);
	p3d_viewer.setParameter('BackgroundColor2', p3d.background2);
	p3d_viewer.setParameter('RenderMode', 'textureflat');
	p3d_viewer.setParameter('ProgressBar', 'off');
	p3d_viewer.setParameter('Renderer', 'webgl'); 
	window.wp.hooks.doAction( '3dprint.viewerConfig');

	p3d_viewer.onloadingstarted = function() {
		p3dDisplayUserDefinedProgressBar(true);
	};
	p3d_viewer.onloadingcomplete = p3d_viewer.onloadingaborted = p3d_viewer.onloadingerror = function() {

		p3dDisplayUserDefinedProgressBar(false);
		if(logoTimerID > 0) return;

		// show statistics of current model when loading is completed
		var scene = p3d_viewer.getScene();
		if(scene && scene.getChildren().length > 0) {
			var objects = scene.getChildren();
			var totalFaceCount = 0;
			var totalVertexCount = 0
			for(var i=0; i<objects.length; i++) {
				totalFaceCount += objects[i].faceCount;
				totalVertexCount += objects[i].vertexBuffer.length / 3;
			}
			var stats = totalVertexCount.toString() + ' vertices' + '<br/>' + totalFaceCount.toString() + ' faces';
			document.getElementById('p3d-statistics').innerHTML = stats;

			if (typeof(jQuery('input[name=product_coating]:checked').closest('li').data('color'))!=='undefined' && jQuery('input[name=product_coating]:checked').closest('li').data('color').length>0 )
				p3dChangeModelColor(jQuery('input[name=product_coating]:checked').closest('li').data('color'));
			else
				p3dChangeModelColor(jQuery('input[name=product_filament]:checked').closest('li').data('color'));

			printer_id=jQuery('input[name=product_printer]:checked').data('id')
			p3dMakeGroundPlane();
			p3dDrawPrinterBox(scene, printer_id, jQuery('input[name=p3d_unit]:checked').val());
			// ask the p3d_viewer to apply this change immediately

			p3d_viewer.resetScene();
			p3d_viewer.zoomFactor=parseInt(p3d.zoom);
			p3d_viewer.update();
			p3dGetStats();

			p3dInitScaleSlider();
			var p3dRangeSlider = document.getElementById('p3d-scale');

			if (typeof(p3dRangeSlider.noUiSlider)!=='undefined') {
				p3dRangeSlider.noUiSlider.set(100)
			}
			//p3dAnalyseModel(jQuery('#pa_p3d_model').val());
			

		}
		else {
			document.getElementById('p3d-statistics').innerHTML = '';
		}
	window.wp.hooks.doAction( '3dprint.modelLoaded');
	};



	if (jQuery('input[name=get_printer_id]').val())	{
		printer=jQuery('input[name=get_printer_id]').val()
		jQuery.cookie('p3d_printer', printer, { expires: p3d.cookie_expire });
	}
	else if (jQuery.cookie('p3d_printer')!='undefined' && jQuery('#p3d_printer_'+jQuery.cookie('p3d_printer')).length>0) {
		printer=jQuery.cookie('p3d_printer');
	}
	else {
		printer=jQuery('input[name=product_printer]').data('id');

	}

	if (jQuery('input[name=get_material_id]').val()) {
		material=jQuery('input[name=get_material_id]').val()
		jQuery.cookie('p3d_material', material, { expires: p3d.cookie_expire });
	}
	else if (jQuery.cookie('p3d_material')!='undefined' && jQuery('#p3d_material_'+jQuery.cookie('p3d_material')).length>0)	{
		material=jQuery.cookie('p3d_material');
	}
	else {
		material=jQuery('input[name=product_filament]').data('id');
	}
	if (jQuery('input[name=get_coating_id]').val()) {
		coating=jQuery('input[name=get_coating_id]').val()
		jQuery.cookie('p3d_coating', coating, { expires: p3d.cookie_expire });
	}
	else if (jQuery.cookie('p3d_coating')!='undefined' && jQuery('#p3d_coating_'+jQuery.cookie('p3d_coating')).length>0)	{
		coating=jQuery.cookie('p3d_coating');
	}
	else {
		coating=jQuery('input[name=product_coating]').data('id');
	}

	if (jQuery('input[name=get_infill]').val()) {
		infill=jQuery('input[name=get_infill]').val()
		jQuery.cookie('p3d_infill', infill, { expires: p3d.cookie_expire });
	}
	else if (jQuery.cookie('p3d_infill')!='undefined') {
		infill=jQuery.cookie('p3d_infill');
	}
	else {
		infill=jQuery('input[name=product_infill]').data('id');
	}

	if (jQuery('input[name=get_product_model]').val()) {
		product_file=jQuery('input[name=get_product_model]').val();
		jQuery.cookie('p3d_file', product_file, { expires: p3d.cookie_expire });
	}
	else {
		product_file=jQuery.cookie('p3d_file');
	}

	if (jQuery('input[name=get_product_unit]').val()) {
		product_unit=jQuery('input[name=get_product_unit]').val();
		jQuery.cookie('p3d_unit', product_unit, { expires: p3d.cookie_expire });
	}
	else if (jQuery.cookie('p3d_unit')!='undefined') {
		product_unit=jQuery.cookie('p3d_unit');
	}
	else {
		product_unit='mm';
	}

	//if (jQuery.cookie('p3d-stats-material-volume')) jQuery('.p3d-stats').css('visibility', 'visible');

	if (typeof(infill)!='undefined') {
		jQuery('#p3d_infill_'+infill).attr('checked', 'checked');
		p3dSelectInfill(jQuery('#p3d_infill_'+infill).closest('li'));
	}

	if (typeof(printer)!='undefined') {
		jQuery('#p3d_printer_'+printer).attr('checked', 'checked');
		p3dSelectPrinter(jQuery('#p3d_printer_'+printer).closest('li'));
	}
	else {
		jQuery('input[name=product_printer]').first().attr('checked', 'checked')
		p3dSelectPrinter(jQuery('input[name=product_printer]').first());
	}

	if (typeof(material)!='undefined') {
		jQuery('#p3d_material_'+material).attr('checked', 'checked');
		p3dSelectFilament(jQuery('#p3d_material_'+material).closest('li'));
	}
	else {
		jQuery('input[name=product_filament]').first().attr('checked', 'checked')
		p3dSelectFilament(jQuery('input[name=product_filament]').first().closest('li'));
	}

	if (typeof(coating)!='undefined') {
		jQuery('#p3d_coating_'+coating).attr('checked', 'checked');
		p3dSelectCoating(jQuery('#p3d_coating_'+coating).closest('li'));
	}
	else if (jQuery('input[name=product_coating]').length>0) {
		jQuery('input[name=product_coating]').first().attr('checked', 'checked');
		p3dSelectCoating(jQuery('input[name=product_coating]').first().closest('li'));
	}

	if (typeof(coating)!='undefined') {
		jQuery('#p3d_coating_'+coating).attr('checked', 'checked');
		p3dSelectCoating(jQuery('#p3d_coating_'+coating).closest('li'));
	}




	if (typeof(product_file)!='undefined') {
		jQuery('#pa_p3d_model').val(product_file);
		p3d_viewer.setParameter('SceneUrl', p3d.upload_url+product_file);
	}
	if (typeof(product_unit)!='undefined') {
		jQuery("input[name=p3d_unit][value=" + product_unit + "]").attr('checked', 'checked');
		p3dSelectUnit(jQuery("input[name=p3d_unit][value=" + product_unit + "]"));
	}
	else {
		p3dSelectUnit(jQuery("input[name=p3d_unit][value=mm]"));
	}

	if (typeof(printer)!='undefined' && typeof(material)!='undefined' && typeof(product_file)!='undefined') {
		p3dGetStats();
	}
	else {
		jQuery('#p3d-file-loading').hide();
		jQuery('#p3d-quote-loading').css('visibility', 'hidden');
	}
	p3d_viewer.init();
	p3d_viewer.update();
}

jQuery(document).ready(function(){

window.p3d_uploader = new plupload.Uploader({
	runtimes : 'html5,flash,silverlight,browserplus,gears,html4',
	browse_button : 'p3d-pickfiles', // you can pass an id...
	multi_selection: false,
	multiple_queues : false,
	max_file_count : 1,
	max_file_size: p3d.file_max_size+"mb",
	container: document.getElementById('p3d-container'), 
	url : p3d.url,
	chunk_size : '2mb',
	flash_swf_url : p3d.plugin_url+'includes/ext/plupload/Moxie.swf',
	silverlight_xap_url : p3d.plugin_url+'includes/ext/plupload/Moxie.xap',
	filters : {
	mime_types: [
		{
			title : p3d.file_extensions+" files", 
			extensions : p3d.file_extensions
		}
	]
	},
	init: {
		QueueChanged: function(p3d_uploader) {
			if(p3d_uploader.files.length > 1)
			{
				jQuery('#p3d-filelist').html('');
				
				p3d_uploader.files.splice(0, 1);
			}
		},
		PostInit: function() {
			document.getElementById('p3d-filelist').innerHTML = '';
			document.getElementById('p3d-console').innerHTML = '';

		},
		Browse: function () {

		},
		FilesAdded: function(up, files) {
			p3d.bar_progress = 0;
			jQuery('.p3d-mail-success').hide();
			jQuery('.p3d-mail-error').hide();
			jQuery('#p3d-repair-status').hide();

			window.wp.hooks.doAction( '3dprint.filesAdded');
			if (p3d.filereader_supported) {
				var file = files[0].getNative();
				var file_ext = file.name.split('.').pop().toLowerCase();

				if (file_ext != 'zip') {
					p3d.filereader_supported = true;
					var reader = new FileReader();
					reader.onload = function(event) {
						var chars  = new Uint8Array(event.target.result);
						var CHUNK_SIZE = 0x8000; 
						var index = 0;
						var length = chars.length;
						var result = '';
						var slice;
						while (index < length) {
							slice = chars.subarray(index, Math.min(index + CHUNK_SIZE, length)); 
							result += String.fromCharCode.apply(null, slice);
							index += CHUNK_SIZE;
						}


						window.wp.hooks.doAction( '3dprint.fileRead');
						theScene = new JSC3D.Scene

						if (file_ext=='stl') {
							stl_loader = new JSC3D.StlLoader()
							stl_loader.parseStl(theScene, result)
						}
						else if (file_ext=='obj') {
							obj_loader = new JSC3D.ObjLoader()
							obj_loader.parseObj(theScene, result)
						}
						else alert('Unsupported format');
	
						jQuery('#p3d-file-loading').hide();
	
		                		p3d_viewer.replaceSceneFromUrl("");//hack to empty the sceneUrl
				                p3d_viewer.init()
				                p3d_viewer.replaceScene(theScene)


						if (typeof(jQuery('input[name=product_coating]:checked').closest('li').data('color'))!=='undefined' && jQuery('input[name=product_coating]:checked').closest('li').data('color').length>0 )
							p3dChangeModelColor(jQuery('input[name=product_coating]:checked').closest('li').data('color'));
						else
							p3dChangeModelColor(jQuery('input[name=product_filament]:checked').closest('li').data('color'));
				                p3dMakeGroundPlane();
				                p3dDrawPrinterBox(p3d_viewer.getScene(), jQuery('input[name=product_printer]:checked').data('id'), jQuery('input[name=p3d_unit]:checked').val());
				                p3d_viewer.resetScene();
						p3d_viewer.zoomFactor=parseInt(p3d.zoom);
				                p3d_viewer.update()
						p3dGetStats();
				                var scene = p3d_viewer.getScene();
				                var objects = scene.getChildren();
				                var totalFaceCount = 0;
				                var totalVertexCount = 0
				                for(var i=0; i<objects.length; i++) {
				                	totalFaceCount += objects[i].faceCount;
				                	totalVertexCount += objects[i].vertexBuffer.length / 3;
	                			}
				                var stats = totalVertexCount.toString() + ' vertices' + '<br/>' + totalFaceCount.toString() + ' faces';
				                document.getElementById('p3d-statistics').innerHTML = stats;
						if (p3d_viewer.getScene() !== null) p3dInitScaleSlider();
						var p3dRangeSlider = document.getElementById('p3d-scale');

						if (typeof(p3dRangeSlider.noUiSlider)!=='undefined') {
							p3dRangeSlider.noUiSlider.set(100)
						}

            				}
            
					reader.readAsArrayBuffer(file);
				} //!zip
					else p3d.filereader_supported = false; //zip file
        		}
		        plupload.each(files, function(file) {
		        	document.getElementById('p3d-filelist').innerHTML += '<div id="' + file.id + '">' + file.name + ' (' + plupload.formatSize(file.size) + ') <b></b></div>';
		        });
		        p3d_uploader.disableBrowse(true);
//		        jQuery('.p3d-stats').hide();
			if (p3d.filereader_supported && p3d_viewer.getScene() !== null) {
				p3dInitScaleSlider();
				var p3dRangeSlider = document.getElementById('p3d-scale');
				if (typeof(p3dRangeSlider.noUiSlider)!=='undefined') {
					p3dRangeSlider.noUiSlider.set(100)
				}
			}
		        jQuery('#price-container').css('visibility', 'hidden');
		        jQuery('#add-cart-container').css('visibility', 'hidden');
		        jQuery('#p3d-console').hide();
		        jQuery('#p3d-file-loading').show();
		        jQuery('#p3d-quote-loading').css('visibility', 'visible');

		        up.start();
			p3d.uploading = true;
		        jQuery('#p3d-pickfiles').click();
		},



		UploadProgress: function(up, file) {
			p3d.bar_progress=parseFloat(file.percent/100);
			document.getElementById(file.id).getElementsByTagName('b')[0].innerHTML = '<span>' + file.percent + "%</span>";
		},

		UploadComplete: function(up, file, response) {
			p3d.uploading = false;
			p3d_uploader.disableBrowse(false);
		},

		Error: function(up, err) {
			p3d.uploading = false;
			p3d_uploader.disableBrowse(false);
			document.getElementById('p3d-console').appendChild(document.createTextNode("\nError #" + err.code + ": " + err.message));
			window.p3dProgressButton._stop();
			jQuery('#p3d-console').show();
		}
	}
});

p3d_uploader.bind('BeforeUpload', function (up, file) {
	up.settings.multipart_params = {
		"action" : 'p3d_handle_upload',
		"quote_attributes" : jQuery('.woo_attribute').serialize(),
		"product_id" : jQuery('#p3d_product_id').val(),
		"printer_id" : jQuery('input[name=product_printer]:checked').data('id'),
		"material_id" : jQuery('input[name=product_filament]:checked').data('id'),
		"coating_id" : jQuery('input[name=product_coating]:checked').data('id'),
		"unit" : jQuery('input[name=p3d_unit]:checked').val() 
	}
	window.wp.hooks.doAction( '3dprint.beforeUpload');
	});

p3d_uploader.init();

p3d_uploader.bind('FileUploaded', function(p3d_uploader,file,response) {
	p3d.uploading = false;
	var data = jQuery.parseJSON( response.response );
	jQuery('#price-container span.amount').html('&nbsp;');
	if (typeof(data.error)!=='undefined') { //fatal error
		jQuery('#p3d-console').html(data.error.message).show();
		jQuery('#p3d-file-loading').hide();
		jQuery('#p3d-quote-loading').css('visibility', 'hidden');
		return false;
  	}
	jQuery('#p3d-quote-loading').css('visibility', 'hidden');
        jQuery('#add-cart-container').css('visibility','visible');
	jQuery('.p3d-mail-success').remove();
	jQuery('.p3d-mail-error').remove();

	if (!p3d.filereader_supported) p3d_viewer.replaceSceneFromUrl(p3d.upload_url+data.filename);

	p3dShowResponse(data);

	jQuery.cookie('p3d_file',data.filename, { expires: p3d.cookie_expire });
	product_file=data.filename;
	jQuery('#pa_p3d_model').val(product_file);
	jQuery('.p3d-stats').css('visibility','visible');

	p3d_viewer.update();

	p3dGetStats();

        if (p3d_viewer.getScene() !== null) p3dInitScaleSlider();
	var p3dRangeSlider = document.getElementById('p3d-scale');
	if (typeof(p3dRangeSlider.noUiSlider)!=='undefined') {
		p3dRangeSlider.noUiSlider.set(100)
	}
	if (p3dCheckPrintability()) {
		jQuery('#price-container').css('visibility','visible');
		jQuery('#add-cart-container').css('visibility','visible');
	}
	p3dAnalyseModel (data.filename);
	//p3dRepairModel (data.filename);


	window.wp.hooks.doAction( '3dprint.fileUploaded');
});



jQuery( "form.variations_form" ).bind( "submit", function() {
	//get resize scale
	jQuery('#p3d-resize-scale').val(p3d.resize_scale);
	var no_request = jQuery('#no_request').val();
	if (no_request == '0') {
		jQuery('input[name=add-to-cart]').remove();
	}
	//screenshot of the current product
	jQuery('#p3d-thumb').val(window.p3d_canvas.toDataURL().replace('data:image/png;base64,',''));
	window.wp.hooks.doAction( '3dprint.productScreenshot');
	return true;
})




});



function p3dRepairModel (filename) {

	if (p3d.api_repair!='on') return;
	if (typeof(filename)=='undefined' || filename.length==0) return;
	var file_ext = filename.split('.').pop().toLowerCase();

	if (file_ext!='stl') { 
		jQuery('#p3d-console').html(p3d.text_cant_process_obj).show(); 
	        jQuery('#add-cart-container').css('visibility', 'hidden');
	        jQuery('#p3d-quote-loading').css('visibility', 'hidden');

		return;
	} 

	jQuery('#p3d-repair-status').show();
	jQuery('#p3d-repair-message').html(p3d.text_repairing_model);
	jQuery('#p3d-repair-image').show();

        jQuery('#add-cart-container').css('visibility', 'hidden');
        jQuery('#p3d-quote-loading').css('visibility', 'visible');

	jQuery.ajax({
		method: "POST",
		url: p3d.url,
		data: { action: "p3d_handle_repair", filename: filename }
		})
		.done(function( msg ) {
			var data = jQuery.parseJSON( msg );
			jQuery('#p3d-repair-image').hide();

			if (p3d.api_analyse!='on') {
			        jQuery('#add-cart-container').css('visibility', 'visible');
			        jQuery('#p3d-quote-loading').css('visibility', 'hidden');
			}

			
			if (typeof(data.filename)!=='undefined' && data.filename.length>0) {
				p3d_viewer.replaceSceneFromUrl(p3d.upload_url+data.filename);
				jQuery.cookie('p3d_file',data.filename, { expires: p3d.cookie_expire });
				jQuery('#pa_p3d_model').val(data.filename);
				jQuery('#p3d-repair-message').html(p3d.text_model_repaired);
			}
			else {
				jQuery('#p3d-repair-message').html(p3d.text_model_repair_failed);
				if (typeof(data.error)!=='undefined') { 
					jQuery('#p3d-repair-message').html(p3d.text_model_repair_failed + ' : ' + data.error.message);
				}
			}

		});
	
}

function p3dAnalyseModel (filename) {
	clearInterval(p3d.refresh_interval);	
	clearInterval(p3d.refresh_interval1);	
	p3d.refresh_interval1_running = false;

	if(p3d.xhr1 && p3d.xhr1.readyState != 4) {
		p3d.xhr1.abort();
		jQuery('#p3d-repair-status').hide();
	}
	if(p3d.xhr2 && p3d.xhr2.readyState != 4) {
		p3d.xhr2.abort();
		jQuery('#p3d-analyse-status').hide();
		jQuery('#stats-material-volume-loading, #stats-material-weight-loading').hide();
	}

	if (p3d.api_analyse!='on') return;
	if (typeof(filename)=='undefined' || filename.length==0) return;
	if (p3d.uploading) return;

//	console.log(typeof(filename));
	var file_ext = filename.split('.').pop().toLowerCase();
	if (file_ext!='stl') { 
		if  (p3d.pricing=='checkout') {
			jQuery('#p3d-console').html(p3d.text_cant_process_obj).show(); 
			jQuery('#add-cart-wrapper .variations_button').hide();
			jQuery('#p3d-request-form').show();
		        jQuery('#price-container').css('visibility', 'hidden');
			jQuery('#p3d-analyse-status').hide();
			jQuery('#stats-material-volume-loading, #stats-material-weight-loading').hide();
			jQuery('#no_request').val('0');
		}
		return;
	}
	else {
		if  (p3d.pricing=='checkout') {
			jQuery('#p3d-console').html('').hide(); 
			jQuery('#add-cart-wrapper .variations_button').show();
			jQuery('#p3d-request-form').hide();
			jQuery('#no_request').val('1');
		}
	}

	if  (p3d.pricing=='checkout') {
	        jQuery('#add-cart-container').css('visibility', 'hidden');
	        jQuery('#p3d-quote-loading').css('visibility', 'visible');
	}
        jQuery('#price-container').css('visibility', 'hidden');




	var layer_height = jQuery('input[name=product_printer]:checked').data('layer-height');
	var wall_thickness = jQuery('input[name=product_printer]:checked').data('wall-thickness');
	var nozzle_size = jQuery('input[name=product_printer]:checked').data('nozzle-size');
	var infill = jQuery('input[name=product_infill]:checked').data('id');
	var filament_diameter = jQuery('input[name=product_filament]:checked').data('diameter');
	var unit = jQuery('#pa_p3d_unit').val();
	if (typeof(infill) == 'undefined') return false;
	if (typeof(layer_height) == 'undefined') return false;

	jQuery('#p3d-analyse-status').show();
	jQuery('#p3d-analyse-message').html(p3d.text_analysing_model);
	jQuery('#p3d-analyse-image').show();
	jQuery('#p3d-analyse-percent').html('1%');
	jQuery('#stats-material-volume, #stats-weight').hide();
	jQuery('#stats-material-volume-loading, #stats-material-weight-loading').show();
	if  (p3d.pricing=='checkout') {
	        jQuery('#add-cart-container').css('visibility', 'hidden');
	        jQuery('#p3d-quote-loading').css('visibility', 'visible');
	}

        jQuery('#price-container').css('visibility', 'hidden');

	p3d.xhr1=jQuery.ajax({
		method: "POST",
		url: p3d.url,
		data: { action: "p3d_handle_analyse", 
			filename: filename, 
			layer_height: layer_height,	
			wall_thickness: wall_thickness,
			nozzle_size: nozzle_size,
			infill: infill,
			scale: p3d.resize_scale,
			unit: unit,
			filament_diameter: filament_diameter
		      }
		})
		.done(function( msg ) {
			var data = jQuery.parseJSON( msg );
			var server = data.server;
			if (typeof(data.error)!=='undefined') {
				jQuery('#p3d-console').html(data.error.message).show();
				jQuery('#p3d-quote-loading').css('visibility', 'hidden');
				jQuery('#p3d-repair-status').hide();
				jQuery('#p3d-analyse-status').hide();

				jQuery('#stats-material-volume-loading, #stats-material-weight-loading').hide();

				return false;

			}

			if (data.status == '2') { //in progress
			        jQuery('#price-container').css('visibility', 'hidden');
				if (p3d.pricing=='checkout') {
				        jQuery('#add-cart-container').css('visibility', 'hidden');
				        jQuery('#p3d-quote-loading').css('visibility', 'visible');
				}

				jQuery('#p3d-analyse-percent').html('10%');
				p3d.refresh_interval = setInterval(function(){
				    p3danalyseCheck(filename, server); 
				}, 3000);
				
			}
			else if (data.status == '0') { //failed
				jQuery('#p3d-repair-message').html(p3d.text_model_analyse_failed);
				if (typeof(data.error)!=='undefined') { 
					jQuery('#p3d-repair-message').html(p3d.text_model_analyse_failed + ' : ' + data.error.message);
				}
			}

		});
	
}


function p3danalyseCheck(filename, server) {

	var layer_height = jQuery('input[name=product_printer]:checked').data('layer-height');
	var wall_thickness = jQuery('input[name=product_printer]:checked').data('wall-thickness');
	var nozzle_size = jQuery('input[name=product_printer]:checked').data('nozzle-size');
	var infill = jQuery('input[name=product_infill]:checked').data('id');
	var filament_diameter = jQuery('input[name=product_filament]:checked').data('diameter');
	var unit = jQuery('#pa_p3d_unit').val();

	if  (p3d.pricing == 'checkout') {
	        jQuery('#add-cart-container').css('visibility', 'hidden');
	        jQuery('#p3d-quote-loading').css('visibility', 'visible');
	}

        jQuery('#price-container').css('visibility', 'hidden');

	p3d.xhr2=jQuery.ajax({
		method: "POST",
		url: p3d.url,
		data: { action: "p3d_handle_analyse_check", 
			filename: filename, 
			server: server,
			layer_height: layer_height,	
			wall_thickness: wall_thickness,
			nozzle_size: nozzle_size,
			infill: infill,
			scale: p3d.resize_scale,
			unit: unit
		      }
		})
		.done(function( msg ) {
			var data = jQuery.parseJSON( msg );

			if (typeof(data.error)!=='undefined') {
				jQuery('#p3d-analyse-status').show();
				jQuery('#p3d-analyse-message').html(p3d.text_model_analyse_failed);
				jQuery('#p3d-analyse-image').hide();

				jQuery('#p3d-console').html(data.error.message).show();
				jQuery('#p3d-file-loading').hide();
				jQuery('#p3d-quote-loading').css('visibility', 'hidden');
				//jQuery('#stats-material-volume, #stats-weight').hide();
				jQuery('#stats-material-volume-loading, #stats-material-weight-loading').hide();
				jQuery('#p3d-analyse-percent').html('');

				clearInterval(p3d.refresh_interval);
			}
			if (data.status=='1') {
				jQuery('#stats-material-volume').html((data.model_filament/1000).toFixed(2));
				window.model_total_volume = data.model_filament;
				jQuery('#p3d-analyse-status').show();
				jQuery('#p3d-analyse-message').html(p3d.text_model_analysed);
				jQuery('#p3d-analyse-image').hide();
				jQuery('#add-cart-container').css('visibility', 'visible');
			        jQuery('#p3d-quote-loading').css('visibility', 'hidden');
			        jQuery('#price-container').css('visibility', 'visible');
				jQuery('#stats-material-volume, #stats-weight').show();
				jQuery('#stats-material-volume-loading, #stats-material-weight-loading').hide();
				jQuery('#p3d-analyse-percent').html('100%');
				p3dGetStats();
				clearInterval(p3d.refresh_interval);
			}
			if (data.status=='2') {
				jQuery('#p3d-analyse-percent').html(data.progress+'%');
			}

		});
	

//clearInterval(p3d.refresh_interval);
}

function p3dBoxFitsBox (dim_x1, dim_y1, dim_z1, dim_x2, dim_y2, dim_z2) {
	var fits=true;
	var min_dim1=Math.min(dim_x1, dim_y1, dim_z1);
	var min_dim2=Math.min(dim_x2, dim_y2, dim_z2);
	var max_dim1=Math.max(dim_x1, dim_y1, dim_z1);
	var max_dim2=Math.max(dim_x2, dim_y2, dim_z2);
	var diag1=Math.sqrt(dim_x1 + dim_y1 + dim_z1);
	var diag2=Math.sqrt(dim_x2 + dim_y2 + dim_z2);
	var median1=(dim_x1 + dim_y1 + dim_z1)/3;
	var median2=(dim_x2 + dim_y2 + dim_z2)/3;

	if (min_dim1<=min_dim2 && max_dim1<=max_dim2 && diag1 <= diag2) 
		fits = true;
	else 
		fits = false;


	fits=window.wp.hooks.applyFilters('3dprint.boxFitsBox', fits, dim_x1, dim_y1, dim_z1, dim_x2, dim_y2, dim_z2);
	return fits;
}

function p3dBoxFitsBoxXY (dim_x1, dim_y1, dim_x2, dim_y2) {
	var fits=true;
	var min_dim1=Math.min(dim_x1, dim_y1);
	var min_dim2=Math.min(dim_x2, dim_y2);
	var max_dim1=Math.max(dim_x1, dim_y1);
	var max_dim2=Math.max(dim_x2, dim_y2);
	var diag1=Math.sqrt(dim_x1 + dim_y1);
	var diag2=Math.sqrt(dim_x2 + dim_y2);

	if (min_dim1<=min_dim2 && max_dim1<=max_dim2) 
		fits = true;
	else 
		fits = false;



	fits=window.wp.hooks.applyFilters('3dprint.boxFitsBoxXY', fits, dim_x1, dim_y1, dim_x2, dim_y2);
	return fits;
}

function p3dShowError(message) {
	var decoded = jQuery('#p3d-console').html(message).text();
	jQuery('#p3d-console').html(decoded).show();
	window.wp.hooks.doAction( '3dprint.showError');
}

function p3dInitProgressButton () {
	if (!p3dDetectIE()) {
		window.p3dProgressButton=new ProgressButton(document.getElementById('p3d-pickfiles'), {
			callback : function( instance ) {
				interval = setInterval( function() {
					instance._setProgress( p3d.bar_progress );
					if( parseInt(p3d.bar_progress) === 1 ) {
						instance._stop(1);
						clearInterval( interval );
					}
				}, 200 );
			}
		} );
	}

}

jQuery(document).ready(function() {
	p3dInitProgressButton();
        jQuery('nav.applePie').easyPie();
	jQuery('nav.applePie ul.nav').show();

});

function p3dChangeModelColor(color) {

	if (p3d_viewer.getScene()==null) return false;

	var objects = p3d_viewer.getScene().getChildren();
	for(var i=0; i<objects.length; i++) {
		if ( objects[i].name!='printerbox' && objects[i].name!='groundplane' && (typeof(objects[i].mtl)=='undefined' || objects[i].mtl=='')) {
			objects[i].setMaterial(new JSC3D.Material('', 0, color.replace('#','0x'), 0, true));		
		}
	}
	p3d_viewer.update();

};

function p3dSelectFilament(obj) {

	jQuery(obj).find('input[type=radio]').attr('checked','true');
	jQuery('#pa_p3d_material').val(jQuery(obj).find('input').data('id'));
	material_id=jQuery(obj).find('input').data('id');

	if (typeof(jQuery('input[name=product_coating]:checked').closest('li').data('color'))!=='undefined' && jQuery('input[name=product_coating]:checked').closest('li').data('color').length>0 )
		p3dChangeModelColor(jQuery('input[name=product_coating]:checked').closest('li').data('color'));
	else
		p3dChangeModelColor(jQuery(obj).attr('data-color'));

	jQuery.cookie('p3d_material', jQuery(obj).find('input').attr('data-id'), { expires: p3d.cookie_expire });

	//check compatible printers
	var compatible_printers = new Array();
	jQuery('input[name=product_printer]').each(function() {
		var materials = jQuery(this).data('materials')+'';
		var materials_array = materials.split(',');
			if (materials.length>0 && jQuery.inArray(material_id+'', materials_array)==-1) {
			jQuery(this).prop('disabled', true);
			jQuery(this).prop('checked', false);
			jQuery(this).css('visibility', 'hidden');
			jQuery(this).parent().find('.p3d-dropdown-item').addClass('p3d-inactive-dropdown-item');
		}
		else {
			jQuery(this).prop('disabled', false);
			jQuery(this).css('visibility', 'visible');
			jQuery(this).parent().find('.p3d-dropdown-item').removeClass('p3d-inactive-dropdown-item');
			compatible_printers.push(this);

		}
	});

	//check if a compatible printer is already selected
	var selected = false;
	for (i=0;i<compatible_printers.length;i++) {
		if (jQuery('#pa_p3d_printer').val()==jQuery(compatible_printers[i]).data('id'))
			selected = true;
	}
	if (!selected && compatible_printers.length>0) {
		jQuery(compatible_printers[0]).prop('checked', true);		
		p3dSelectPrinter(jQuery(compatible_printers[0]).parent());
	}

	//check compatible coatings
	var compatible_coatings = new Array();

	jQuery('input[name=product_coating]').each(function() {
		var materials = jQuery(this).data('materials')+'';
		var materials_array = materials.split(',');

			if (materials.length>0 && jQuery.inArray(material_id+'', materials_array)==-1) {
			jQuery(this).prop('disabled', true);
			jQuery(this).prop('checked', false);
			jQuery(this).css('visibility', 'hidden');
			jQuery(this).parent().find('.p3d-dropdown-item').addClass('p3d-inactive-dropdown-item');
			if (jQuery(this).parent().hasClass('p3d-color-item')) jQuery(this).parent().addClass('p3d-inactive-color-item');

		}
		else {
			jQuery(this).prop('disabled', false);
			jQuery(this).css('visibility', 'visible');
			jQuery(this).parent().find('.p3d-dropdown-item').removeClass('p3d-inactive-dropdown-item');
			if (jQuery(this).parent().hasClass('p3d-color-item')) jQuery(this).parent().removeClass('p3d-inactive-color-item');
			compatible_coatings.push(this);

		}
	});

	//check if a compatible coating is already selected
	var selected = false;
	for (i=0;i<compatible_coatings.length;i++) {
		if (jQuery('#pa_p3d_coating').val()==jQuery(compatible_coatings[i]).data('id'))
			selected = true;
	}
	if (!selected && compatible_coatings.length>0) {
		jQuery(compatible_coatings[0]).prop('checked', true);		
		p3dSelectCoating(jQuery(compatible_coatings[0]).parent());
	}




	var material_name=jQuery(obj).find('input').data('name');
	var material_color=jQuery(obj).find('input').data('color');
	if (typeof(document.getElementById('p3d-material-name'))!=='undefined') {
		jQuery('#p3d-material-name').html(p3d.text_material+' : <div style="background-color:'+material_color+'" class="color-sample"></div>'+material_name);
	}

	if (jQuery(obj).hasClass('p3d-color-item')) {
		jQuery(obj).closest('.p3d-fieldset').find('.p3d-color-item').removeClass('p3d-active');
		jQuery(obj).addClass('p3d-active');
	}
	
	p3dGetStats();
	//if (p3d_viewer.getScene() !== null) p3dInitScaleSlider();
	p3dCheckPrintability();
	window.wp.hooks.doAction( '3dprint.selectFilament');
}

function p3dSelectCoating(obj) {
	if (jQuery(obj).find('input[type=radio]').prop('disabled')) return false;
	jQuery(obj).find('input[type=radio]').attr('checked','true');
	jQuery('#pa_p3d_coating').val(jQuery(obj).find('input').data('id'));
	coating_id=jQuery(obj).find('input').data('id');

	if (typeof(jQuery(obj).attr('data-color'))!=='undefined' && jQuery(obj).attr('data-color').length>0) {
		p3dChangeModelColor(jQuery(obj).attr('data-color'));
	}
	else {
		p3dChangeModelColor(jQuery('input[name=product_filament]:checked').closest('li').data('color'));
	}

	jQuery.cookie('p3d_coating', jQuery(obj).find('input').attr('data-id'), { expires: p3d.cookie_expire });

	var coating_name=jQuery(obj).find('input').data('name');
	var material_color=jQuery(obj).find('input').data('color');
	if (typeof(document.getElementById('p3d-coating-name'))!=='undefined') {
		jQuery('#p3d-coating-name').html(p3d.text_coating+' : <div style="background-color:'+material_color+'" class="color-sample"></div>'+coating_name);
	}

	if (jQuery(obj).hasClass('p3d-color-item')) {
		jQuery(obj).closest('.p3d-fieldset').find('.p3d-color-item').removeClass('p3d-active');
		jQuery(obj).addClass('p3d-active');
	}

	p3dGetStats();
	window.wp.hooks.doAction( '3dprint.selectCoating');
}

function p3dSelectUnit(obj) {
	jQuery(obj).attr('checked','true');
	jQuery('#p3d_unit').val(jQuery(obj).val());
	jQuery('#pa_p3d_unit').val(jQuery(obj).val());
	/*
	if (product_unit=='mm' && jQuery(obj).val()=='inch') {
		p3d_viewer.zoomFactor*=2.54
		p3d_viewer.update()
	}
	else if (product_unit=='inch' && jQuery(obj).val()=='mm') {
		p3d_viewer.zoomFactor/=2.54
		p3d_viewer.update()
	}
	*/
	product_unit=jQuery(obj).val();
	jQuery.cookie('p3d_unit', jQuery(obj).val(), { expires: p3d.cookie_expire });
	printer_id=jQuery('input:radio[name=product_printer]:checked').data('id');
	p3dChangePrinter(printer_id);
	p3dGetStats();
	if (p3d_viewer.getScene() !== null) p3dInitScaleSlider();
	p3dAnalyseModel(jQuery('#pa_p3d_model').val());
	window.wp.hooks.doAction( '3dprint.selectUnit');
}

function p3dChangePrinter(printer_id) {
	if (p3d_viewer.getScene()==null) return false;
	var scene = p3d_viewer.getScene();
	var objects = scene.getChildren();
	for(var i=0; i<objects.length; i++) {
		if ( objects[i].name=='printerbox' ) {
			scene.removeChild(objects[i]);
		}
	}

	p3dDrawPrinterBox(scene, printer_id, jQuery('input[name=p3d_unit]:checked').val());
}
function p3dSelectPrinter(obj) {
	if (jQuery(obj).find('input[type=radio]').prop('disabled')) return false;
	jQuery(obj).find('input[type=radio]').attr('checked','true');
	jQuery('#pa_p3d_printer').val(jQuery(obj).find('input').data('id'));
	jQuery.cookie('p3d_printer', jQuery(obj).find('input').data('id'), { expires: p3d.cookie_expire });
	printer_id=jQuery(obj).find('input').data('id');
	var printer_name=jQuery(obj).find('input').data('name');
	if (typeof(document.getElementById('p3d-printer-name'))!=='undefined') {
		jQuery('#p3d-printer-name').html(p3d.text_printer+' : '+printer_name);
	}
	p3dChangePrinter(printer_id);
	



	//check compatible infills
	var compatible_infills = new Array();
	jQuery('input[name=product_infill]').each(function() {
		var infills = jQuery(obj).find('input').data('infills')+'';
		var infills_array = infills.split(',');

		if (infills.length>0 && jQuery.inArray(jQuery(this).data('id')+'', infills_array)==-1) {
			jQuery(this).prop('disabled', true);
			jQuery(this).prop('checked', false);
//			jQuery(this).css('visibility', 'hidden');
			jQuery(this).hide();
			jQuery(this).parent().find('.p3d-dropdown-item').hide();
		}
		else {
			jQuery(this).prop('disabled', false);
//			jQuery(this).css('visibility', 'visible');

			if (!jQuery(this).hasClass('p3d-infill-dropdown'))
				jQuery(this).show();	
			jQuery(this).parent().find('.p3d-dropdown-item').show();
			compatible_infills.push(this);

		}
	});
	//check if a compatible infill is already selected
	var selected = false;
	for (i=0;i<compatible_infills.length;i++) {
		if (jQuery('#pa_p3d_infill').val().length>0 && jQuery('#pa_p3d_infill').val()==jQuery(compatible_infills[i]).data('id')) {
			selected = true;
		}

	}

	if (!selected && compatible_infills.length>0) {
		var default_infill = jQuery(obj).find('input').data('default-infill');
		for (i=0;i<compatible_infills.length;i++) {
			if (jQuery(compatible_infills[i]).data('id') == default_infill) {
				jQuery(compatible_infills[i]).prop('checked', true);		
				p3dSelectInfill(jQuery(compatible_infills[i]).parent());
			}

		}
	}




	p3dGetStats();
	if (p3d_viewer.getScene() !== null) p3dInitScaleSlider();
	p3dCheckPrintability();
	p3dAnalyseModel(jQuery('#pa_p3d_model').val());
	window.wp.hooks.doAction( '3dprint.selectPrinter');
}

function p3dSelectInfill (obj) {

	if (jQuery(obj).find('input[type=radio]').prop('disabled')) return false;	
	jQuery(obj).find('input[type=radio]').attr('checked','true');
	jQuery('#pa_p3d_infill').val(jQuery(obj).find('input').data('id'));
	jQuery.cookie('p3d_infill', jQuery(obj).find('input').data('id'), { expires: p3d.cookie_expire });

	infill_id=jQuery(obj).find('input').data('id');
	var infill_name=jQuery(obj).find('input').data('name');
	if (typeof(document.getElementById('p3d-infill-name'))!=='undefined') {
		jQuery('#p3d-infill-name').html(p3d.text_infill+' : '+infill_name);
	}
	p3dAnalyseModel(jQuery('#pa_p3d_model').val());
}

function p3dCheckPrintability() {
//todo: many things
	var printable=true;
	var x_dim=parseFloat(jQuery('#stats-length').html());
	var y_dim=parseFloat(jQuery('#stats-width').html());
	var z_dim=parseFloat(jQuery('#stats-height').html());

	if (!x_dim || !y_dim || !z_dim) return false;

	var printer_width=parseFloat(jQuery('input:radio[name=product_printer]:checked').attr('data-width'));
	var printer_length=parseFloat(jQuery('input:radio[name=product_printer]:checked').attr('data-length'));
	var printer_height=parseFloat(jQuery('input:radio[name=product_printer]:checked').attr('data-height'));

	if (!p3dBoxFitsBox(x_dim*10, y_dim*10, z_dim*10, printer_width, printer_length, printer_height)) {
		p3dShowError(p3d.error_box_fit);
		printable=false;
	}
	else if (!p3dBoxFitsBoxXY(x_dim*10, y_dim*10, printer_width, printer_length)) {
		p3dShowError(p3d.warning_box_fit);
	}

	if (!printable) { 
		jQuery('#price-container').css('visibility','hidden');
		jQuery('#add-cart-container').css('visibility','hidden');
	}
	else { 
		jQuery('#printer_fit_error').hide();
	}

	printable=window.wp.hooks.applyFilters('3dprint.checkPrintability', printable);

	return printable;
}

function p3dCalculatePrintingCost( product_info ) {
	var material = jQuery('input[name=product_filament]:checked');
	var coating = jQuery('input[name=product_coating]:checked');
	var printer = jQuery('input[name=product_printer]:checked');


	var material_cost = 0;
	var coating_cost = 0;
	var printing_cost = 0;

	printing_volume=product_info['model']['material_volume'];

	if ( !isNaN ( material.data('price') ) ) {
		if ( material.data('price_type')=='cm3' ) {
			material_cost=( printing_volume )*material.data('price');
		}
		else if ( material.data('price_type')=='gram' ) {
			material_cost=product_info['model']['weight']*material.data('price');
		}
	}
	else if ( material.data('price').indexOf(':')>-1 ) {

		var material_volume_pricing_array = material.data('price').split(';');
		for (var i = 0; i < material_volume_pricing_array.length; i++) {
			var discount_rule = material_volume_pricing_array[i].split(':');
			if (discount_rule.length == 2) {
				var amount = discount_rule[0];
				var price = discount_rule[1];	
				if ( material.data('price_type')=='cm3' ) {
					if (printing_volume >= amount ) 
						material_cost = printing_volume * price;
				}
				else if ( material.data('price_type')=='gram' ) {
					if (product_info['model']['weight'] >= amount) 
						material_cost = product_info['model']['weight'] * price;
				}
			}
		}
	}

	if ( !isNaN ( printer.data('price') ) ) {
		if ( printer.data('price_type')=="material_volume" ) {
			printing_cost=printing_volume*printer.data('price');
		}
		else if ( printer.data('price_type')=="box_volume" ) {
			printing_cost=product_info['model']['box_volume']*printer.data('price');
		}
		else if ( printer.data('price_type')=="gram" ) {
			printing_cost=product_info['model']['weight']*printer.data('price');
		}
		else if ( printer.data('price_type')=="sla" ) {
      // voeg volumefactor toe
      var printer_volume_pricing_string = `
        0:4.641;
        64000:3.24;
        125000:2.42;
        216000:1.9;
        343000:1.6;
        512000:1.5;
        729000:1.4;
        1000000:1.2;
        1728000:1.1
      `;

      var printer_volume_pricing_array = printer_volume_pricing_string.split(';');
        for (var i = 0; i < printer_volume_pricing_array.length; i++) {
          var discount_rule = printer_volume_pricing_array[i].split(':');
          if (discount_rule.length == 2) {
            var amount = discount_rule[0];
            var price = discount_rule[1];	
            // put box_volume in cubic cm, convert to mm > * 1000
            if (product_info['model']['box_volume']*1000 >= amount)
              printing_vol = product_info['model']['box_volume'] * price;
          }
        }
      printing_cost = 50.50 + printing_vol * printer.data('price');
		}
		else if ( printer.data('price_type')=="sls" ) {
			//printing_cost=product_info['model']['weight']*printer.data('price')*1000000000000;
      // added dimensions

      // standaard dimensies zijn in cm, converteren naar mm
      var x = product_info['model']['x_dim'] * 100;
      var y = product_info['model']['y_dim'] * 100;
      var z = product_info['model']['z_dim'] * 100;
      var xyz = x * y * z;

      var calcSLS = function(multiplier) {
        if (z * z< x * y) {
            printing_cost = (3.14 + ( 0.0023 * x * z))+ ( (0.042 * (( x*y)/(x*10))) * (z-1)) * multiplier;
        } else
        if (x < z) {
            printing_cost = (3.14 + ( 0.0023 * x * z))+ ( (0.042 * (( z*y)/(z*10))) * (x-1)) * multiplier;
        } else
        {
            printing_cost = (3.14 + ( 0.0023 * x * y))+ ( (0.042 * (( x*z)/(x*10))) * (y-1)) * multiplier;
        }
      };


      //if smaller than x use following multiplier
      //use . for a comma
      //use : to split x and multiplier
      //use ; to define end of multiplier and start a new comparison
      var multiplierString = `
      1001:-0.3;
      8001:0.3;
      27001:0.5;
      64001:0.62;
      125001:0.8;
      216001:0.9;
      343001:1.05;
      512001:1.2;
      729001:1.32;
      1000001:1.47;
      1331001:1.75;
      2197001:1.9;
      2744001:2.05;
      3375001:2.15;
      4096001:2.3;
      4913001:2.45;
      5832001:2.6;
      6859001:2.75;
      8000001:2.95;
      15625001:4;
      120000001:5;
      `

      var multiplierArray = multiplierString.split(';');
      for (var i = 0; i < multiplierArray.length; i++) {
        var helper = multiplierArray[i].split(':');
        if (xyz < helper[0]) {
          calcSLS(helper[1]);
          break;
        }
      }
		}

	}
	else if ( printer.data('price').indexOf(':')>-1 ) {
		var printer_volume_pricing_array = printer.data('price').split(';');
		for (var i = 0; i < printer_volume_pricing_array.length; i++) {
			var discount_rule = printer_volume_pricing_array[i].split(':');
			if (discount_rule.length == 2) {
				var amount = discount_rule[0];
				var price = discount_rule[1];	
				if ( printer.data('price_type')=='material_volume' ) {
					if (printing_volume >= amount)
						printing_cost = printing_volume * price;
				}
				else if ( printer.data('price_type')=='box_volume' ) {
					if (product_info['model']['box_volume'] >= amount)
						printing_cost = product_info['model']['box_volume'] * price;
				}
				else if ( printer.data('price_type')=='gram' ) {
					if (product_info['model']['weight'] >= amount)
						printing_cost = product_info['model']['weight'] * price;
				}
			}
		}
	}

	if (typeof(coating.data('price'))!=='undefined') {
		if ( !isNaN ( coating.data('price') ) ) {
			coating_cost = product_info['model']['surface_area'] * coating.data('price');
		}
		else if ( coating.data('price').indexOf(':')>-1 ) {
			var surface_area_pricing_array = coating.data('price').split(';');
			for (var i = 0; i < surface_area_pricing_array.length; i++) {
				var discount_rule = surface_area_pricing_array[i].split(':');
				if (discount_rule.length == 2) {
					var amount = discount_rule[0];
					var price = discount_rule[1];	
					if (product_info['model']['surface_area'] >= amount) {
						coating_cost = product_info['model']['surface_area'] * price;
					}
				}
			}
		}
	}

	jQuery( ".woo_attribute" ).each(function() {
		var attr_price=parseFloat(jQuery(this).find('option:selected').data('price'));
		if (isNaN(attr_price)) attr_price = 0;
		var attr_price_type=jQuery(this).find('option:selected').data('price-type');
		var attr_pct_type=jQuery(this).find('option:selected').data('pct-type');

		if (typeof(attr_pct_type)!=='undefined' && attr_price_type=='pct') {
			if (attr_pct_type=='printer') {	
				printing_cost+=(printing_cost/100)*attr_price
			}
			else if (attr_pct_type=='material') {	
				material_cost+=(material_cost/100)*attr_price
			}
			else if (attr_pct_type=='coating') {	
				coating_cost+=(coating_cost/100)*attr_price

			}
		}

	})


	var total=printing_cost+material_cost+coating_cost;

	jQuery( ".woo_attribute" ).each(function(){
		var attr_price=parseFloat(jQuery(this).find('option:selected').data('price'));
		if (isNaN(attr_price)) attr_price = 0;
		var attr_price_type=jQuery(this).find('option:selected').data('price-type');
		var attr_pct_type=jQuery(this).find('option:selected').data('pct-type');

		if (attr_price_type=='flat') {
			total+=attr_price;
		}
		else if (typeof(attr_pct_type)!=='undefined' && attr_price_type=='pct' && attr_pct_type=='total') {
			total+=(total/100)*attr_price;
		}
	})

	if (total < parseFloat(p3d.min_price)) total = parseFloat(p3d.min_price);
	total=window.wp.hooks.applyFilters('3dprint.calculatePrintingCost', total, product_info);
	return total;
}
//an example hook
window.wp.hooks.addFilter( '3dprint.calculatePrintingCost', function  (total, product_info) {
	//do something with total
	return total;
})

function p3dGetStatsClientSide() {
	var scene=window.p3d_viewer.getScene();
	scene.calcAABB();
	var aabb=p3d.aabb;

	if (p3d.api_analyse=='on')  //scaled on the server
		var filament_volume = (window.model_total_volume/1000); //cm3
	else
		var filament_volume = (window.model_total_volume/1000)*Math.pow(p3d.resize_scale,3); //cm3

	var surface_area = (window.model_surface_area/100)*Math.pow(p3d.resize_scale,2); //cm2
	var model_x = (Math.abs(aabb.maxX-aabb.minX)/10)*p3d.resize_scale
	var model_y = (Math.abs(aabb.maxY-aabb.minY)/10)*p3d.resize_scale
	var model_z = (Math.abs(aabb.maxZ-aabb.minZ)/10)*p3d.resize_scale


	var box_volume = model_x*model_y*model_z; 
	var material_coeff = 100; //%

	jQuery( ".woo_attribute" ).each(function() {
		var attr_price=parseFloat(jQuery(this).find('option:selected').data('price'));
		if (isNaN(attr_price)) attr_price = 0;
		var attr_price_type=jQuery(this).find('option:selected').data('price-type');
		var attr_pct_type=jQuery(this).find('option:selected').data('pct-type');

		if (typeof(attr_pct_type)!=='undefined' && attr_price_type=='pct') {
			if (attr_pct_type=='material_amount') {	
				material_coeff+=attr_price
			}

		}

	})


	if (product_unit=='inch') {
		model_x = model_x*2.54;
		model_y = model_y*2.54;
		model_z = model_z*2.54;
		surface_area=surface_area*6.4516;
		box_volume = model_x*model_y*model_z; 
		if (p3d.api_analyse!='on') {
			filament_volume = filament_volume*16.387064;
		}
	}

	var product_info = new Array();
	product_info['model'] = new Array();
	product_info['model']['x_dim'] = parseFloat(model_x.toFixed(2));
	product_info['model']['y_dim'] = parseFloat(model_y.toFixed(2));
	product_info['model']['z_dim'] = parseFloat(model_z.toFixed(2));
	product_info['model']['material_volume'] = parseFloat(filament_volume.toFixed(2))*(material_coeff/100);
	product_info['model']['box_volume'] = parseFloat(box_volume.toFixed(2));
	product_info['model']['surface_area'] = parseFloat(surface_area.toFixed(2));
	product_info['model']['weight'] = parseFloat(filament_volume * parseFloat(jQuery('input[name=product_filament]:checked').data('density')) * (material_coeff/100));
	product_info=window.wp.hooks.applyFilters('3dprint.getStatsClientSide', product_info);
	return product_info;
}
function p3dGetStats() {
//	jQuery('.p3d-stats').hide(); 
	jQuery('#price-container').css('visibility','hidden');
	jQuery('#add-cart-container').css('visibility','hidden');
	jQuery('#p3d-console').html('').hide();


	var printer_id=jQuery('input:radio[name=product_printer]:checked').attr('data-id');
	var material_id=jQuery('input:radio[name=product_filament]:checked').attr('data-id');
	if (typeof(jQuery('input:radio[name=product_coating]:checked').attr('data-id'))!=='undefined')
		var coating_id=jQuery('input:radio[name=product_coating]:checked').attr('data-id');
	else 
		var coating_id='';
	var product_id=jQuery('#p3d_product_id').val();
	var model=jQuery('#pa_p3d_model').val();
	var model_unit=jQuery("input[name=p3d_unit]:checked").val();

	if (typeof(window.p3d_viewer)!=='undefined' && window.p3d_viewer.isLoaded) {
		var product_info=p3dGetStatsClientSide();

		var product_price=p3dCalculatePrintingCost(product_info);
		var response = new Array();
		response.model = new Array();
		response.model = product_info['model'];
		response.price = product_price.toFixed(p3d.price_num_decimals);

		if (p3d.currency_position=='left')
			accounting.settings.currency.format = "%s%v";
		else if (p3d.currency_position=='left_space')
			accounting.settings.currency.format = "%s %v";
		else if (p3d.currency_position=='right')
			accounting.settings.currency.format = "%v%s";
		else if (p3d.currency_position=='right_space')
			accounting.settings.currency.format = "%v %s";

		response.html_price = accounting.formatMoney(product_price, p3d.currency_symbol, p3d.price_num_decimals, p3d.thousand_sep, p3d.decimal_sep);
		jQuery('#p3d_estimated_price').val(response.price);
		p3dShowResponse(response);

	}
	window.wp.hooks.doAction( '3dprint.getStats');

}

function p3dShowResponse(response) {
	if (response.error) { //fatal error
		jQuery('#p3d-quote-loading').css('visibility', 'hidden'); 
		p3dShowError(response.error);
		return;
	}
	if (window.p3d_uploader.state==1) jQuery('#p3d-quote-loading').css('visibility', 'hidden');
	if (response.model) {
		if (response.model.error) p3dShowError(response.model.error); //soft error
		jQuery('#stats-material-volume').html(response.model.material_volume.toFixed(2));
		jQuery('#stats-box-volume').html(response.model.box_volume.toFixed(2));
		jQuery('#stats-surface-area').html(response.model.surface_area.toFixed(2));
		jQuery('#stats-width').html(response.model.x_dim.toFixed(2));
		jQuery('#stats-length').html(response.model.y_dim.toFixed(2));
		jQuery('#stats-height').html(response.model.z_dim.toFixed(2));
		jQuery('#stats-weight').html(response.model.weight.toFixed(2));

		jQuery('#scale_x').val(response.model.x_dim.toFixed(2));
		jQuery('#scale_y').val(response.model.y_dim.toFixed(2));
		jQuery('#scale_z').val(response.model.z_dim.toFixed(2));




		jQuery('.p3d-stats').css('visibility','visible');
	}

	if (p3dCheckPrintability() && !((p3d.xhr1 && p3d.xhr1.readyState != 4) || (p3d.xhr2 && p3d.xhr2.readyState != 4)) ) {

		if (p3d.pricing!='request') {
			jQuery('#price-container').css('visibility','visible');
		}
		if (window.p3d_uploader.state==1) jQuery('#add-cart-container').css('visibility','visible');
		if (window.p3d_uploader.state==1 || !p3d.filereader_supported) jQuery('#add-cart-container').css('visibility','visible');
		jQuery('#price-container meta[itemprop=price]').attr('content',response.price);
		jQuery('#price-container span.amount').html(response.html_price);
	}
	window.wp.hooks.doAction( '3dprint.showResponse');
}
function p3dCalculateWeight(material_volume) {
	var density = parseFloat(jQuery('input[name=product_filament]:checked').attr('data-density'));
	var weight = material_volume*density;
	return weight.toFixed(2);
}


function p3dDisplayUserDefinedProgressBar(show) {
	if(show) {
		jQuery('#p3d-file-loading').show();
	}
	else {
		jQuery('#p3d-file-loading').hide();
	}
}

function p3dDetectIE() {
	var ua = window.navigator.userAgent;

	var msie = ua.indexOf('MSIE ');
	if (msie > 0) {
        // IE 10 or older => return version number
        return parseInt(ua.substring(msie + 5, ua.indexOf('.', msie)), 10);
    }

    var trident = ua.indexOf('Trident/');
    if (trident > 0) {
        // IE 11 => return version number
        var rv = ua.indexOf('rv:');
        return parseInt(ua.substring(rv + 3, ua.indexOf('.', rv)), 10);
    }

    var edge = ua.indexOf('Edge/');
    if (edge > 0) {
       // IE 12 => return version number
       return parseInt(ua.substring(edge + 5, ua.indexOf('.', edge)), 10);
   }

    // other browser
    return false;
}


function p3dDrawPrinterBox(scene, printer_id, product_unit) {
	var printer_dim=new Array();
	printer_dim.x=jQuery('#p3d_printer_'+printer_id).data('length')
	printer_dim.y=jQuery('#p3d_printer_'+printer_id).data('width')
	printer_dim.z=jQuery('#p3d_printer_'+printer_id).data('height')

	var scene=window.p3d_viewer.getScene();
	scene.calcAABB();
	var sceneBox=p3d.aabb;

	var planeCenter = scene.aabb.center();

	var model_xdim=sceneBox.maxX - sceneBox.minX;
	var model_ydim=sceneBox.maxY - sceneBox.minY;
	var model_zdim=sceneBox.maxZ - sceneBox.minZ;

	var model_xdiff=printer_dim.x - model_xdim;
	var model_ydiff=printer_dim.y - model_ydim;

	//xy rotation


	if (model_xdim > model_ydim && printer_dim.y > printer_dim.x) {
		tmpvar=printer_dim.x;
		printer_dim.x=printer_dim.y;
		printer_dim.y=tmpvar;
	}

	if (model_ydim > model_xdim && printer_dim.x > printer_dim.y) {
		tmpvar=printer_dim.y;
		printer_dim.y=printer_dim.x;
		printer_dim.x=tmpvar;
	}




//todo: z-rotation ?

	printer_dim.x=printer_dim.x/p3d.resize_scale;
	printer_dim.y=printer_dim.y/p3d.resize_scale;
	printer_dim.z=printer_dim.z/p3d.resize_scale;

	if (product_unit=='inch') {
		printer_dim.x=printer_dim.x/2.54;
		printer_dim.y=printer_dim.y/2.54;
		printer_dim.z=printer_dim.z/2.54;
	}

	var visible = true;
	if (p3d.printer_color=='') visible = false;
	var printerBox = new JSC3D.Mesh('printerbox', visible);
	printerBox.vertexBuffer = [ 
		planeCenter[0] - printer_dim.x/2, planeCenter[1] - printer_dim.y/2, sceneBox.minZ, 
		planeCenter[0] - printer_dim.x/2, planeCenter[1] + printer_dim.y/2, sceneBox.minZ, 
		planeCenter[0] + printer_dim.x/2, planeCenter[1] + printer_dim.y/2, sceneBox.minZ, 
		planeCenter[0] + printer_dim.x/2, planeCenter[1] - printer_dim.y/2, sceneBox.minZ,

		planeCenter[0] - printer_dim.x/2, planeCenter[1] - printer_dim.y/2, sceneBox.minZ + printer_dim.z, 
		planeCenter[0] - printer_dim.x/2, planeCenter[1] + printer_dim.y/2, sceneBox.minZ + printer_dim.z, 
		planeCenter[0] + printer_dim.x/2, planeCenter[1] + printer_dim.y/2, sceneBox.minZ + printer_dim.z, 
		planeCenter[0] + printer_dim.x/2, planeCenter[1] - printer_dim.y/2, sceneBox.minZ + printer_dim.z
	];
	printerBox.indexBuffer = [ 
		0, 1, 2, 3, -1, 
		4, 5, 6, 7, -1, 
		0, 4, 0, 0, -1,
		1, 5, 1, 1, -1,
		2, 6, 2, 2, -1,
		3, 7, 3, 3, -1,
		3, 7, 3, 3, -1

	];
	printerBox.isDoubleSided = true;	
	printerBox.setRenderMode('wireframe');
	printerBox.setMaterial(new JSC3D.Material('plane', 0, p3d.printer_color, 0));
	printerBox.init();
	scene.addChild(printerBox);
	scene.calcAABB();
	p3d_viewer.update();
	window.wp.hooks.doAction( '3dprint.drawPrinterBox');
}

function p3dMakeGroundPlane() {

	var printer_dim=new Array();
	printer_dim.x=jQuery('input[name=product_printer]:checked').data('length')
	printer_dim.y=jQuery('input[name=product_printer]:checked').data('width')
	printer_dim.z=jQuery('input[name=product_printer]:checked').data('height')
	var scene = p3d_viewer.getScene();
	var sceneBox=p3d.aabb;

	var planeCenter = scene.aabb.center();
	var planeHalfSize = 1.5 * Math.max(sceneBox.maxX, sceneBox.maxY, sceneBox.minX+printer_dim.x, sceneBox.minY+printer_dim.y);
	var planeMinX = planeCenter[0] - planeHalfSize;
	var planeMinY = planeCenter[1] - planeHalfSize;
	var planeZ = sceneBox.minZ;
	var numOfGridsPerDimension = 10;
	var sizePerGrid = 2 * planeHalfSize / numOfGridsPerDimension;

	var visible = true;
	if (p3d.plane_color=='') visible = false;

	var groundPlane = new JSC3D.Mesh('groundplane', visible);

	groundPlane.vertexBuffer = [];
	for (var i=0; i<=numOfGridsPerDimension; i++) {
		for (var j=0; j<=numOfGridsPerDimension; j++) {
			groundPlane.vertexBuffer.push(planeMinX + j * sizePerGrid, planeMinY + i * sizePerGrid, planeZ );
		}
	}

	groundPlane.indexBuffer = [];
	for (var i=0; i<numOfGridsPerDimension; i++) {
		for (var j=0; j<numOfGridsPerDimension; j++) {
			groundPlane.indexBuffer.push(
				i * (numOfGridsPerDimension + 1) + j, 
				(i + 1) * (numOfGridsPerDimension + 1) + j, 
				(i + 1) * (numOfGridsPerDimension + 1) + j + 1, 
				i * (numOfGridsPerDimension + 1) + j + 1, 
				-1 
				);
		}
	}

	groundPlane.isDoubleSided = true;	
	groundPlane.init();
	groundPlane.calcAABB();

	groundPlane.setRenderMode('wireframe');
	groundPlane.setMaterial(new JSC3D.Material('plane', 0, p3d.plane_color, 1));
	scene.addChild(groundPlane);
	scene.calcAABB();
}

function p3dSignedVolume(p1, p2, p3) {

	v321 = p3[0]*p2[1]*p1[2];
	v231 = p2[0]*p3[1]*p1[2];
	v312 = p3[0]*p1[1]*p2[2];
	v132 = p1[0]*p3[1]*p2[2];
	v213 = p2[0]*p1[1]*p3[2];
	v123 = p1[0]*p2[1]*p3[2];
	return (1.0/6.0)*(-v321 + v231 + v312 - v132 - v213 + v123);
}

function p3dSurfaceArea(p1, p2, p3) {

	ax = p2[0] - p1[0];
	ay = p2[1] - p1[1];
	az = p2[2] - p1[2];
	bx = p3[0] - p1[0];
	by = p3[1] - p1[1];
	bz = p3[2] - p1[2];
	cx = ay*bz - az*by;
	cy = az*bx - ax*bz;
	cz = ax*by - ay*bx;
	return 0.5 * Math.sqrt(cx*cx + cy*cy + cz*cz);
}    

function p3dDialogCheck() {
//file not selected fix
	if (p3d.file_selected>0)
		jQuery('#p3d-container input[type=file]').parent().css('z-index', '999')
	p3d.file_selected++;
}

function p3dInitScaleSlider() {
	window.wp.hooks.doAction( '3dprint.p3dInitScaleSlider_start');
	if (p3d_viewer.getScene()==null) return false;
	var p3dRangeSlider = document.getElementById('p3d-scale');
	var printer_dim=new Array();
	var product_unit = jQuery('input[name=p3d_unit]:checked').val();
	printer_dim.x=jQuery('input[name=product_printer]:checked').data('length');
	printer_dim.y=jQuery('input[name=product_printer]:checked').data('width');
	printer_dim.z=jQuery('input[name=product_printer]:checked').data('height');
	if (product_unit=='inch') {
		printer_dim.x=printer_dim.x/2.54;
		printer_dim.y=printer_dim.y/2.54;
		printer_dim.z=printer_dim.z/2.54;
	}
	var scene = p3d_viewer.getScene();
	var sceneBox=p3d.aabb;

	var model_dim=new Array();
	model_dim.x=sceneBox.maxX-sceneBox.minX
	model_dim.y=sceneBox.maxY-sceneBox.minY
	model_dim.z=sceneBox.maxZ-sceneBox.minZ


	var max_printer_side = Math.max(printer_dim.x, printer_dim.y);
	var min_printer_side = Math.min(printer_dim.x, printer_dim.y);
	var max_model_side = Math.max(model_dim.x, model_dim.y);
	var min_model_side = Math.min(model_dim.x, model_dim.y);

	var height_diff = printer_dim.z/model_dim.z;
	var max_side_diff = max_printer_side/max_model_side;
	var min_side_diff = min_printer_side/min_model_side;
	var side_diff = Math.min(max_side_diff, min_side_diff, height_diff);

	var max_scale = side_diff*100;
	

	if (isNaN(max_scale)) return false;
	if (typeof(p3dRangeSlider.noUiSlider)=='undefined') {

		//if (max_scale < 100) p3d.resize_scale = max_scale;

		noUiSlider.create(p3dRangeSlider, {
			start: [ 100 ],
			range: {
				'min': [  1 ],
				'max': [ max_scale ]
			}
		});
		var p3dRangeSliderValueElement = document.getElementById('p3d-slider-range-value');

		p3dRangeSlider.noUiSlider.on('update', function( values, handle ) {
			p3dRangeSliderValueElement.value = values[handle];
			if (p3d.api_analyse=='on' && jQuery('.noUi-active').length>0) {

				if (!p3d.refresh_interval1_running) {
					p3d.refresh_interval1_running = true;
					p3d.refresh_interval1 = setInterval(function(){
						p3dUpdateScale('');
					}, 500);
				}
			}
			else {
				p3d.resize_scale = values[handle]/100;
				jQuery('#p3d-resize-scale').val(p3d.resize_scale);
				printer_id=jQuery('input:radio[name=product_printer]:checked').data('id');
				p3dChangePrinter(printer_id);
				p3dGetStats();
				if (p3d.resize_scale<1) 
					p3d_viewer.zoomFactor=p3d.zoom;
				else
					p3d_viewer.zoomFactor=parseInt(p3d.resize_scale*p3d.zoom);
				p3dAnalyseModel(jQuery('#pa_p3d_model').val());
				p3d_viewer.update()

			}


		});

	}
	else {

		p3dRangeSlider.noUiSlider.updateOptions({
			start: [ 100 ],
			range: {
				'min': 1,
				'max': max_scale
			}
		});

	}

	p3dGetStats();
	window.wp.hooks.doAction( '3dprint.p3dInitScaleSlider_end');
}
function p3dUpdateScale (value) {
	if (jQuery('.noUi-active').length==0) {
		clearInterval(p3d.refresh_interval1);	
		if (value=='') {
			var p3dRangeSlider = document.getElementById('p3d-scale');
			value = p3dRangeSlider.noUiSlider.get();
		}
		p3d.refresh_interval1_running = false;
		p3d.resize_scale = value/100;
		jQuery('#p3d-resize-scale').val(p3d.resize_scale);
		printer_id=jQuery('input:radio[name=product_printer]:checked').data('id');
		p3dChangePrinter(printer_id);
		p3dGetStats();
		p3dAnalyseModel(jQuery('#pa_p3d_model').val());
		if (p3d.resize_scale<1) 
			p3d_viewer.zoomFactor=p3d.zoom;
		else
			p3d_viewer.zoomFactor=parseInt(p3d.resize_scale*p3d.zoom);
		p3d_viewer.update()
		
	}
}

function p3dUpdateDimensions (obj) {
	window.wp.hooks.doAction( '3dprint.p3dUpdateDimensions_start');
	var cur_value=jQuery(obj).val();
	if (isNaN(cur_value)) return;

	var scene = p3d_viewer.getScene();
	var sceneBox=p3d.aabb;

	var model_dim=new Array();
	model_dim.x=sceneBox.maxX-sceneBox.minX
	model_dim.y=sceneBox.maxY-sceneBox.minY
	model_dim.z=sceneBox.maxZ-sceneBox.minZ
	
	if (jQuery(obj).attr('id')=='scale_x') prev_value = model_dim.x;
	if (jQuery(obj).attr('id')=='scale_y') prev_value = model_dim.y;
	if (jQuery(obj).attr('id')=='scale_z') prev_value = model_dim.z;

	var scale = (cur_value*10)/prev_value;

	var p3dRangeSlider = document.getElementById('p3d-scale');
	if (typeof(p3dRangeSlider.noUiSlider)!=='undefined') {
		p3dRangeSlider.noUiSlider.set(scale*100)
		p3d.resize_scale = scale;
	}

}

function p3dUpdateSliderValue (value) {
	if (isNaN(value)) return false;

	var p3dRangeSlider = document.getElementById('p3d-scale');
	if (typeof(p3dRangeSlider.noUiSlider)!=='undefined') {
		p3dRangeSlider.noUiSlider.set(value);
	}
}

if (window.FileReader && window.FileReader.prototype.readAsArrayBuffer) {
	p3d.filereader_supported=true;
} else {
	p3d.filereader_supported=false;
}

