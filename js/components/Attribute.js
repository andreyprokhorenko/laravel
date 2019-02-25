import { VisualComponent } from '../VisualComponent';
import $ from 'jquery';

const selectors = {
    SELECT_ATTRIBUTE_TYPE: '.js-select-attribute-type',
    SELECT_ATTRIBUTE_FORMAT: '.js-select-attribute-format',
};

class Attribute extends VisualComponent
{
    constructor(container, baseEditForm)
    {
        super(container);
        this.container = container;
        this.firstLoading = true;
        this.initElements();
        this.initEvents();
        this.baseEditForm = baseEditForm;
    }

    initElements()
    {
        this.elements = {
            selectAttributeType: this.container.find(selectors.SELECT_ATTRIBUTE_TYPE),
            selectAttributeFormat: this.container.find(selectors.SELECT_ATTRIBUTE_FORMAT),
        }
    }

    initEvents()
    {
        let self = this;

        self.elements.selectAttributeType.on('change', function () {
            let attributeTypeId;

            Attribute.clearFormatSelect(self);
            attributeTypeId = $(this).find('option:selected').val();

            self.getAttributeFormat(attributeTypeId, self)
        });

        $(document).ready(function () {
            let attributeTypeId;
            attributeTypeId = $(this).find('option:selected').val();
            self.getAttributeFormat(attributeTypeId, self)
        })
    }

    getAttributeFormat(attributeTypeId, self)
    {
        self.lock(self.container);
        $.ajax({
            type: 'GET',
            url: '/admin/reviewsattributes/format/' + attributeTypeId,
        }).done(function (response) {
            let option = '',
                attributeFormatSelect = self.elements.selectAttributeFormat,
                format;

            for (format in response.attributeFormats) {
                option += '<option value="' + format + '">' + response.attributeFormats[format] + '</option>';
            }
            attributeFormatSelect.append(option);

        }).always(function () {
            self.unlock(self.container);
            if (self.firstLoading) {
                self.baseEditForm.formData = self.baseEditForm.container.serialize();
                self.firstLoading = false;
            }
        });
    }

    static clearFormatSelect(self)
    {
        self.elements.selectAttributeFormat.empty();
    }
}

export { Attribute }