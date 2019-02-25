'use strict';

import { VisualComponent } from './VisualComponent';
import { Messenger, MessageType } from './Messenger';
import { AttributeHelper } from '../helpers/AttributeHelper';
import $ from 'jquery';
import { FormValidateHelper } from '../helpers/FormValidateHelper'
import { ImageAttribute } from '../../admin/attributes/imageAttribute'

const selector = {
    BUTTON_SAVE: '.js-save',
    BUTTON_SAVE_ALL_ATTRIBUTES: '.save-all-attributes',
    BUTTON_UPLOAD_IMAGE: '.js-attr-image-upload',
    BUTTON_IMAGE_ATTR_EDIT: '.js-attr-image-edit-btn',
    ATTRIBUTES_CONTAINER: '.js-edit-attributes'
};

class BaseEditForm extends VisualComponent
{
    constructor(container)
    {
        super(container);
        this.formData = '';
        this.container = container;
        this.messanger = new Messenger($('.js-review-messenger'));
        this.initElements();
        this.initEvents();
    }

    initElements()
    {
        this.elements = {
            buttonSave: this.container.find(selector.BUTTON_SAVE),
            buttonSaveAllAttributes: this.container.find(selector.BUTTON_SAVE_ALL_ATTRIBUTES),
            attributesContainer: this.container.find(selector.ATTRIBUTES_CONTAINER)
        };

        this.imageAttribute = null;
    }

    initEvents()
    {
        let self = this;
        let form = self.container,
            attributesContainer = self.elements.attributesContainer;
        let formSubmitting = false;
        window.addEventListener('load', function() {
            self.formData = form.serialize();
            self.unlock(form);
        });
        self.elements.buttonSave.on('click', function () {
            if (!AttributeHelper.requiredAttributesValidation(attributesContainer)) {
                return;
            }

            if (self.elements.buttonSaveAllAttributes.length && self.elements.buttonSaveAllAttributes.is(':visible')) {
                alert('Please save all attributes!');
                return;
            }

            formSubmitting = true;
            self.lock(form);
            let attributeValues = AttributeHelper.collectAttributeValuesByList(form.find('.attributes-list')),
                redirectUrl = $(this).data('redirect');

            self.setAttributesBeforeSave(attributeValues);
            self.checkEditor();
            self.saveEntity(redirectUrl);
        });

        window.addEventListener("beforeunload", function (e) {
            if (formSubmitting || self.formData == self.container.serialize()) {
                return undefined;
            }

            (e || window.event).returnValue = 'Changes you made may not be saved.';
        });

        self.container.on('click', selector.BUTTON_UPLOAD_IMAGE,  function() {
            if (self.imageAttribute === null) {
                self.imageAttribute = new ImageAttribute(self.container);

                self.imageAttribute.initEvents();
            } else {
                self.imageAttribute = new ImageAttribute(self.container);
            }

            self.imageAttribute.uploadImages(this);
        });

        self.container.on('click', selector.BUTTON_IMAGE_ATTR_EDIT,  function() {
            if (self.imageAttribute === null) {
                self.imageAttribute = new ImageAttribute(self.container);

                self.imageAttribute.initEvents();
            }
        });
    }

    saveEntity(redirectUrl)
    {
        let self = this,
            form = this.container,
            formData = new FormData(form[0]);

        $.ajax({
            type: form.attr('method'),
            url: form.attr('action'),
            processData: false,
            contentType: false,
            data: formData
        }).done(function (response) {
            FormValidateHelper.clearErrors(form);
            if (redirectUrl) {
                window.location.href = redirectUrl;
            } else {
                self.messanger.addMessage(response.message, MessageType.SUCCESS);
                BaseEditForm.scrollToTop();
            }
        }).fail(function (xhr) {
            if (xhr.status === 422) {
                self.messanger.addMessage(xhr.responseJSON.message, MessageType.ERROR);
                FormValidateHelper.clearErrors(form);
                FormValidateHelper.showValidationErrors(form, xhr.responseJSON.errors);
                BaseEditForm.scrollToTop();
            }
        }).always(function () {
            self.unlock(form);
        });
    }

    setAttributesBeforeSave(attributes)
    {
        let self = this;
        if (!attributes.length) {
            return;
        }
        $.each(attributes, function (index, value) {
            if (!value) {
                return;
            }
            let input = `<input type='hidden' name='attributes[${index}]' value='${value}'>`;
            self.container.append(input);
        });
    }

    static scrollToTop()
    {
        window.scrollTo(0, 0);
    }

    checkEditor()
    {
        let self = this;
        if (!($('textarea').is('.js-ckeditor'))) {
           return;
        }

        self.container.find('textarea.js-ckeditor').each(function(index, element){
            let elementIdVal = $(element).attr('id');
            $(element).html(CKEDITOR.instances[elementIdVal].getData());
        });
    }
}

export { BaseEditForm }