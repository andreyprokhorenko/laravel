import { BaseEditForm } from '../components/BaseEditForm';
import { Attribute } from '../components/attribute/Attribute';

$(function() {
    let baseEditForm = new BaseEditForm($('.js-review-attributes-edit-form'));
    let attribute = new Attribute($('.js-review-attributes-edit-form'), baseEditForm);
});