/*
 * jQuery PhotoTag plugin 1.3
 *
 * Copyright (c) 2012 Karl Mendes
 * http://karlmendes.com
 *
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *
 * Revision:
*/

(function($) {

	$.fn.photoTag = function( options ){

		var defaultOptions = {
			requestTagsUrl: 'photo-tags.php',
			deleteTagsUrl: 'delete.php',
			addTagUrl: 'add-tag.php',
			parametersForNewTag: {
				name: {
					parameterKey: 'name',
					isAutocomplete: true,
					autocompleteUrl: 'names.php',
					label: 'Name'
				}
			},
			parametersForRequest : ['image-id','album-id'],
			literals:{
				communicationProblem: 'Communication problem, your changes could not be saved',
				saveTag: 'Ok',
				cancelTag: 'Cancel',
				addNewTag: 'Add new tag',
				removeTag: 'Remove tag'
			},
			tag: {
				tagIdParameter: 'tag-id',
				defaultWidth: 100,
				defaultHeight: 100,
				isResizable: true,
				minWidth: 50,
				minHeight: 50,
				maxWidth: 150,
				maxHeight: 150,
				cssClass: 'photoTag-tag',
				idPrefix: 'photoTag-tag_',
				showDeleteLinkOnTag: true,
				deleteLinkCssClass: 'photoTag-delete',
				deleteLinkIdPrefix: 'photoTag-delete_',
				flashAfterCreation: true,
				newTagFormWidth: 120,
				newTagFormClass: 'photoTag-newTagForm'
			},
			imageWrapBox: {
				cssClass: 'photoTag-wrap',
				idPrefix: 'photoTag-wrap_',
				addNewLinkIdPrefix: 'photoTag-add_',
				controlPaneIdPrefix: 'photoTag-cpanel_',
				showTagList: true,
				tagListCssClass: 'photoTag-taglist',
				tagListIdPrefix: 'photoTag-taglist_',
				tagListRemoveItemIdPrefix: 'photoTag-removeTag',
				canvasIdPrefix: 'photoTag-canvas_',
				controlPanelHeight: 25
			},
			showAddTagLinks: true,
			externalAddTagLinks: {
				bind: false,
				selector: ".addTag"
			},
			isEnabledToEditTags: true,
			manageError: 'internal function, user can bind a new one. function(response)',
			beforeTagRequest: 'bind by user, function( parameters )'
		};

		var cache = {
			tags: {}
		};

		var options = $.extend({},defaultOptions,options);

		var getParametersForImage = function( imageElement ){
			var parameters = {};
			$.each(options.parametersForRequest,function(i, key){
				var parameterValue = imageElement.attr('data-'+key);
				if(parameterValue)
					parameters[key] = parameterValue;
			});
			return parameters;
		};

		var registerEventsForTagBox = function( tagBox ){
			tagBox.mouseover(
				function(){
					if(!$.browser.msie)
						$(this).stop().animate({ opacity: 1.0 }, 500);
					else
						$(this).css({ opacity: 1.0 });
				}).mouseout(
				function(){
					if(!$.browser.msie)
						$(this).stop().animate({ opacity: 0.0 }, 500);
					else
						$(this).css({ opacity: 0.0 });
			});

		};

		var manageError = function( response ){
			if( $.isFunction(options.manageError) )
				options.manageError(response);
			else{
				if(response.message)
					alert(response.message);
				else
					alert(options.literals.communicationProblem);
			}
		};

		var registerEventsForDeleteLink = function( link, image ){
			link.click(
				function(e){
					e.preventDefault();
					var tagId = link.attr('href').substring(1);
					var parameters = getParametersForImage(image);
					parameters[options.tag.tagIdParameter] = tagId;
					$.getJSON(options.deleteTagsUrl,parameters,
						function( data ){
							if(!data.result)
								manageError(data);
						}
					);
					$('#' + options.tag.deleteLinkIdPrefix + tagId).parent().remove();
					$('#' + options.imageWrapBox.tagListRemoveItemIdPrefix + tagId).parent().remove();
				}
			);
		}

		var registerEventsForAddTagLink = function( link, image, image_id ){
			$(link).click(function(e){
				e.preventDefault();
				if($('#' + options.tag.idPrefix + 'temp').length == 0){
					hideAllTags(image_id);
					$('#' + options.imageWrapBox.idPrefix + image_id).append(createTempTag(image));
					prepareTempTagBox($('#' + options.tag.idPrefix + 'temp'),image,image_id);
				}
			});
		};

		var dragOrResizeEventHandler = function( e, ui ){
			var tagPosition = $(this).position();
			var x = tagPosition.left;
			var y = tagPosition.top;
			if($("#tempTagBoxForm")){
				$("#tempTagBoxForm").css({
					'position':'absolute',
					'top':y + $(this).height() + 10,
					'left':x
				});
			}
		}

		var prepareTempTagBox = function( tempTagBox, image, image_id ){
			tempTagBox.draggable({
				containment: image,
				cursor: 'move',
				drag: dragOrResizeEventHandler
			});
			tempTagBox.resizable({
				maxHeight: options.tag.maxHeight,
				maxWidth: options.tag.maxWidth,
				minHeight: options.tag.minHeight,
				minWidth: options.tag.minWidth,
				containment: image,
				resize: dragOrResizeEventHandler
			});
			createNewTagForm(tempTagBox,image,image_id);
		};

		var createNewTagForm = function( tempTagBox, image, image_id ){
			var form = $('<form id="tempNewTagForm" action="'+options.addTagUrl+'"></form>');
			var newTagFormBox = $('<div id="tempTagBoxForm" class="photoTagForm"></div>');
			var tempTagBoxPosition = $(tempTagBox).position();
			newTagFormBox.css({
				'position':'absolute',
				'top': tempTagBoxPosition.top + tempTagBox.height() + 10,
				'left': tempTagBoxPosition.left,
				'width' : options.tag.newTagFormWidth
			});
			newTagFormBox.append($('<div id="tempNewTagFormContent" class="content main wrap"></div>'));
			var imageWrapper = $("#" + options.imageWrapBox.idPrefix + image_id);
			imageWrapper.append(newTagFormBox);
			$('#tempNewTagFormContent').append(form);
			$.each(options.parametersForNewTag,function( i, properties ){
				var input = $('<input type="text" autocomplete="off" id="tempInput_'+i+'" name="'+properties.parameterKey+'">');
				if(properties.label){
					var label = $('<label></label>');
					var div = $('<div/>');
					label.append(properties.label);
					$('#tempNewTagForm').append(label);
				};
					$('#tempNewTagForm').append(input);
				if(properties.isAutocomplete){
					$('#tempInput_'+i).parent().append($('<input name="'+properties.parameterKey+'_id" id="hidden_tempInput_'+i+'" type="hidden"/>'));
					$('#tempInput_'+i).autocomplete({
						source:properties.autocompleteUrl,
						select: function( event, ui){
							$('#hidden_tempInput_'+i).val(ui.item.id);
						}
					});
				}
			});
			var submit = $('<input class="inputSubmit" type="submit" value="' + options.literals.saveTag + '" />');
			$('#tempNewTagForm').append(submit);
			var hiddenInput = $("<input type='hidden' name='image_id' value ='" + image_id + "' />");
			$('#tempNewTagForm').append(hiddenInput);
			var cancel = $('<input class="inputCancel" type="button" value="' + options.literals.cancelTag + '"/>');
			cancel.click(function(e){
				e.preventDefault();
				removeNewTempTag();
				showAllTags(image_id);
			});
			$('#tempNewTagForm').append(cancel);
			$('#tempNewTagForm').submit(function(e){
				e.preventDefault();
				var tempTagBox = $('#'+options.tag.idPrefix+'temp');
				var tag = {
					left: tempTagBox.position().left,
					top: tempTagBox.position().top,
					width: tempTagBox.width(),
					height: tempTagBox.height()
				}
				$.getJSON(options.addTagUrl+'?'+$.param(tag) + '&' + $(this).serialize(),function(response){
					if(response.result != undefined && !response.result){
						manageError(response);
						return;
					}
					var tagBox = createTagBoxFromJSON(response.tag,image);
					$('#' + options.imageWrapBox.idPrefix + image_id).append(tagBox);
					extendTagBoxAttributes(tagBox,response.tag,image,image_id);
				});
				removeNewTempTag();
				showAllTags(image_id);
			});

		};

		var removeNewTempTag = function(){
			$('#'+options.tag.idPrefix+'temp').remove();
			$('#tempTagBoxForm').remove();
		};

		var createTagBox = function( tagId, dimension, position, opacity ){
			var tagBox = $('<div class="'+ options.tag.cssClass +'" id="' + options.tag.idPrefix + tagId +'"></div>');
			var css = {
				'position': 'absolute',
				'top': position.top + 'px',
				'left': position.left + 'px',
				'height': dimension.height + 'px',
				'width': dimension.width + 'px',
				'opacity': opacity
			};
			tagBox.css(css);
			return tagBox
		};

		var createTagBoxFromJSON = function( tagJSON, image ){
			if( !(tagJSON.height && tagJSON.width) ){
				tagJSON.height = options.tag.defaultHeight;
				tagJSON.width = options.tag.defaultWidth;
			};
			var dimension = {width: tagJSON.width,height: tagJSON.height};
			var position = {top: tagJSON.top,left: tagJSON.left};
			var tagBox = createTagBox(tagJSON.id,dimension,position,0);
			registerEventsForTagBox(tagBox);
			var innerElement = $("<div class='innerTag'></div>");
			innerElement.append(tagJSON.text);
			tagBox.append(innerElement);
			if(options.isEnabledToEditTags && tagJSON.isDeleteEnable && options.tag.showDeleteLinkOnTag){
				var deleteLink = $('<a id="'+ options.tag.deleteLinkIdPrefix + tagJSON.id +'" class="'+ options.tag.deleteLinkCssClass +'" href="#'+ tagJSON.id +'"></a>');
				registerEventsForDeleteLink(deleteLink,image);
				tagBox.append(deleteLink);
			};
			return tagBox;
		}

		var createTagItemForList = function( tagJSON, image ){
			var item = $('<li></li>');
			if(tagJSON.url){
				var link = $('<a href="'+ tagJSON.url +'">'+ tagJSON.text +'</a>');
				item.append(link);
			}else{
				item.append(tagJSON.text);
			}
			if(tagJSON.isDeleteEnable){
				var deleteLink = $('<a id="'+ options.imageWrapBox.tagListRemoveItemIdPrefix + tagJSON.id +'" class="'+ options.tag.deleteLinkCssClass +'" href="#'+ tagJSON.id +'">'+ options.literals.removeTag +'</a>');
				registerEventsForDeleteLink(deleteLink,image);
				item.append(' (');
				item.append(deleteLink);
				item.append(')');
			}
			return item;
		}

		var createTempTag = function( image, image_id ){
			var dimension = {width: options.tag.defaultWidth,height: options.tag.defaultHeight};
			var position = {
				top: (image.height()/2-dimension.height/2),
				left: (image.width()/2-dimension.width/2)
				};
			cache.tempId++;
			var tempTagBox = createTagBox('temp',dimension,position,1);
			return tempTagBox;
		};

		var hideAllTags = function( image_id ){
			$.each(cache.tags[image_id],function(){
				$(this).css({'opacity':0.0});
				$(this).hide();
			});
		};

		var showAllTags = function( image_id ){
			$.each(cache.tags[image_id],function(){
				$(this).show();
			});
		}

		var createAddTagLink = function( image, image_id ){
			var addTagLink = $('<a id="'+ options.imageWrapBox.addNewLinkIdPrefix + image_id + '" href="#" class="">'+ options.literals.addNewTag +'</a>');
			registerEventsForAddTagLink(addTagLink,image,image_id);
			return addTagLink;
		};

		var wrapImage = function( image, image_id ){
			var imageHeight = image.height();
			var imageWidth = image.width();
			var canvas = $('<div id="' + options.imageWrapBox.canvasIdPrefix + image_id + '" style="position:relative;height:'+ (imageHeight + options.imageWrapBox.controlPanelHeight) +'px;width:'+ imageWidth +'px;"></div>');
			var wrapper = $('<div class="' + options.imageWrapBox.cssClass + '" id="' + options.imageWrapBox.idPrefix + image_id +'" style="position:absolute;top:20px;left:0;height:'+ imageHeight +'px;width:'+ imageWidth +'px;"></div>');
			canvas.append(wrapper);
			var controlPane = $('<div id="'+ options.imageWrapBox.controlPaneIdPrefix + image_id +'"></div>');
			canvas.append(controlPane);
			image.wrap(canvas);
			if(!options.externalAddTagLinks.bind)
				$('#' + options.imageWrapBox.controlPaneIdPrefix + image_id).append(createAddTagLink(image,image_id));
			else{
				var externalAddLinks = $(options.externalAddTagLinks.selector);
				externalAddLinks.each(function(){
					registerEventsForAddTagLink(this,image,image_id);
				});
			}
			var container = $('<div></div>');
			$('#' + options.imageWrapBox.canvasIdPrefix + image_id).wrap(container);
			if(options.imageWrapBox.showTagList){
				var tagList = $('<ul id="'+options.imageWrapBox.tagListIdPrefix+image_id+'" class="'+options.imageWrapBox.tagListCssClass+'"></ul>');
				$('#' + options.imageWrapBox.canvasIdPrefix + image_id).parent().append(tagList);
			}
		}

		var extendTagBoxAttributes = function( tagBox, tagJSON, image, image_id ){
			if(options.tag.flashAfterCreation){
				$(tagBox).css({'opacity':1.0});
				if(!$.browser.msie)
					$(tagBox).stop().animate({ opacity: 0.0 }, 800);
				else
					$(tagBox).css({ opacity: 0.0 });
			};
			if(options.imageWrapBox.showTagList){
				var tagItemForList = createTagItemForList(tagJSON,image);
				$('#'+options.imageWrapBox.tagListIdPrefix+image_id).append(tagItemForList);
			};
		}

		var prepareImage = function( imageDetailsJSON, image ){
			wrapImage(image,imageDetailsJSON.id);
			var cachedInstance = cache.tags[imageDetailsJSON.id] = {};
			$.each(imageDetailsJSON.Tags,function(){
				var tagBox = createTagBoxFromJSON(this,image);
				cachedInstance[this.id] = tagBox;
				$('#' + options.imageWrapBox.idPrefix + imageDetailsJSON.id).append(tagBox);
				extendTagBoxAttributes(tagBox,this,image,imageDetailsJSON.id);
			});
		};

		this.each(function(){

			var $this = $(this);

			var parameters = getParametersForImage($this);

			if( !$.isFunction(options.beforeTagRequest) || options.beforeTagRequest(parameters) ){
				$.getJSON(
					options.requestTagsUrl,
					parameters,
					function( response ){
						if(response.result != undefined && !response.result){
							manageError(response);
							return;
						}
						if(response.options){
							options = $.extend({},options,response.options);
						}
						$.each(response.Image,function(){
							prepareImage(this,$this);
						});
					}
				);
			}

		});

		return this;
	};
})(jQuery);
