class FormValidateHelper
{
    static clearErrors(form)
    {
        let fieldErrorBlock = form.find('div.js-field-error-block');
        form.find('em.error').remove();
        fieldErrorBlock.empty();
        fieldErrorBlock.closest('div.form-group').removeClass('has-error');
    }

    static showValidationErrors(form, errors)
    {
        let errorBlock,
            field,
            name,
            index;

        for (name in errors) {
            errorBlock = form.find('.js-field-error-block[data-name="' + name + '"]');
            field = form.find("[name='" + name + "']");
            field.closest('div.form-group').addClass('has-error');

            for (index in errors[name]) {
                errorBlock.append('<em class="error help-block" id="' + name + '-error"  >' + errors[name][index] + '</em><br>');
            }
        }
    }
}
export { FormValidateHelper }