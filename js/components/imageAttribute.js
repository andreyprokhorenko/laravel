'use strict';

import $ from 'jquery';
import { RefreshLoader } from "../../app/components/RefreshLoader";
import { VisualComponent } from "../../app/components/VisualComponent";

const selector = {
    NEW_ATTRIBUTE_BOX: '.js-new-attribute',
    IMAGE_GROUP: '.js-image-attr-group',
    IMAGE_INPUT: '.js-attr-image-input',
    TOKEN_INPUT: 'input[name="_token"]',
    INFO_BOX: '.js-image-attr-info',
    ERROR_BOX: '.js-image-attr-error',
    DELETE_BTN: '.js-image-attr-delete',
    MAIN_BLOCK: '.js-image-attr-main-block',
    INPUT_FILE: 'input[type="file"]',
};

const classes = {
    MAIN_BLOCK: 'js-image-attr-main-block',
    BOX_SPINNER: 'box-spinner',
    NEW_ATTR: 'new-attribute',
    JS_NEW_ATTR: 'js-new-attribite-input',
    JS_NEW_ATTR_BLOCK: 'js-new-attribute',
    EDIT_VAL: 'edit_val_',
    ATTR_VAL: 'attribute-value',
    JS_ATTR_VAL: 'js-attr-image-value',
    HIDDEN: 'hidden',
    DISPLAY_NONE: 'd-none',
    DELETE_ICON_CLASSES: 'fa fa-times image-attribute-delete-btn position-absolute js-image-attr-delete',
    MARGIN_TOP_3: 'mt-3',
    ROW: 'row',
};

const urls = {
    upload: '/admin/attribute-image/upload',
    delete: '/admin/attribute-image/delete/',
};

const methods = {
    GET: 'GET',
    POST: 'POST',
    DELETE: 'DELETE'
};

const events = {
    CLICK: 'click',
};

class ImageAttribute extends VisualComponent
{
    /**
     * @param {jQuery|string} container
     */
    constructor(container)
    {
        super(container);
        
        this.initElements();
    }

    initElements()
    {
        this.elements = {
            imageInput: this.container.find(selector.IMAGE_INPUT),
            tokenInput: this.container.find(selector.TOKEN_INPUT),
            attributeBox: this.container.find(selector.NEW_ATTRIBUTE_BOX),
            infoBox: this.container.find(selector.INFO_BOX),
            errorBox: this.container.find(selector.ERROR_BOX),     
            imageGroup: this.container.find(selector.IMAGE_GROUP),
            mainBlock: this.container.find(selector.MAIN_BLOCK)
        }

        this.refreshLoader = new RefreshLoader(
            this.elements.imageGroup,
            this.elements.imageGroup
        );

        this.token = this.elements.tokenInput.val();
    }

    initEvents()
    {
        let self = this;

        this.container.on(events.CLICK, selector.DELETE_BTN,  function(event) {
            self.deleteImage($(this));
        });
    }

    /**
     * @param {Object} button
     */
    uploadImages(button)
    {
        this.hideError();

        let self = this,
            data = new FormData,
            attrBox = $(button).parents(selector.NEW_ATTRIBUTE_BOX),
            images = attrBox.find(selector.INPUT_FILE)[0].files,
            mainBox = attrBox.find(selector.MAIN_BLOCK);

        if (images.length === 0) {
            return alert('Please select images');
        }
        data.set('_method', methods.POST);
        data.set('_token', this.token);

        $.each(images, function(index, image) {
            data.append('images[]', image, image.url);
        });

        this.refreshLoader.setContainer($(button).parents(selector.IMAGE_GROUP));
        this.refreshLoader.setLockContainer($(button).parents(selector.IMAGE_GROUP));
        this.refreshLoader.setState(RefreshLoader.state.WAITING);

        $.ajax({
            type: methods.POST,
            url: urls.upload,
            data: data,
            cache: false,
            contentType: false,
            processData: false,
        }).done(function (response) {
            self.generateAttributeInputs(response.images, attrBox);
            self.setImageBoxes(response.html, attrBox);
        }).fail(function (xhr) {
            if (xhr.status === 422) {
                self.showError(xhr.responseJSON.errors);
            }
        }).always(function () {
            self.refreshLoader.setState(RefreshLoader.state.HIDDEN);
        });
    }

    /**
     * @param {Array} images
     * @param {Object} attrBox
     */
    generateAttributeInputs(images, attrBox)
    {
        let self = this;

        images.forEach(function (image, index) {
            let block = $('<div/>', {
                    class: classes.ATTR_VAL + ' ' + classes.HIDDEN + ' ' + classes.JS_ATTR_VAL
                });

            $('<input>', {
                id: classes.EDIT_VAL + index,
                class: classes.NEW_ATTR + ' ' + classes.JS_NEW_ATTR,
                value: image.name,
                hidden: true
            }).appendTo(block);
            attrBox.append(block);
        });
    }

    /**
     * @param {string} html
     * @param {Object} attrBox
     */
    setImageBoxes(html, attrBox)
    {
        let mainBox = attrBox.find(selector.MAIN_BLOCK);

        if (typeof mainBox[0] === 'undefined') {
            mainBox = $('<div/>', {
                class: classes.ROW + ' ' + classes.MAIN_BLOCK + ' ' + classes.MARGIN_TOP_3
            }).appendTo(attrBox);
        }

        mainBox.append(html);
    }

    /**
     * @param {jQuery} icon
     */
    deleteImage(icon)
    {
        let name = icon.data('name'),
            input = $('.' + classes.JS_NEW_ATTR + '[value="' + name + '"]');
            
        if (typeof input[0] !== "undefined") {
            input.parent().remove();
        }

        icon.parent().remove();
    }

    /**
     * @param {Object} errors
     */
    showError(errors)
    {
        let error = '';
        
        $.each(errors, function(index, item) {
            error = item[0];
        });
        
        this.elements.infoBox.addClass(classes.DISPLAY_NONE);
        this.elements.errorBox.text(error)
            .removeClass(classes.DISPLAY_NONE);
    }

    hideError()
    {
        this.elements.errorBox.addClass(classes.DISPLAY_NONE);
        this.elements.infoBox.removeClass(classes.DISPLAY_NONE);
    }
}

export { ImageAttribute }