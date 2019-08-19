<div class="panel panel-default">
    <div class="panel-heading text-center">
        <strong>{{ language.get('nav_admin_result') }}</strong>
    </div>
    {% if error %}
        <div class="alert alert-danger">
            <strong>{{ error }}</strong>
        </div>
    {% endif %}

    {% if success %}
        <div class="alert alert-success">
            <strong>{{ success }}</strong>
        </div>
    {% endif %}

    {% if not error %}
                <form class="form-horizontal" method="post" action="?page=admin/result_upload/save">
                    <fieldset>

                        <!-- Form Name -->
                        <legend class="color-two">{{ event.getName() }} - {{ language.get('general_' ~ event.getType) }}</legend>

                        <!-- Select Basic -->
                        {% for eventAttribute in attributes %}

                            {% set attrId = eventAttribute.getId() %}
                            {% set attrType = eventAttribute.getType() %}
                            {% set betAttrValue = result ? result.getAttributeValueByKey(attrId) : '' %}

                            <div class="form-group">
                                <label class="col-md-4 control-label"
                                       for="selectbasic">{{ language.get('betting_' ~ attrId) }}</label>
                                <div class="col-md-5">
                                    <select id="selectbasic" name="result_attr[{{ attrId }}]"
                                            class="form-control event-{{ event.getId() }}"
                                            {{ event.bet ? ' disabled="disabled"' : '' }}>
                                        <option value="">{{ language.get('admin_result_upload_default_option') }}</option>
                                        {{ formHelper.getSelectOption(attrType).getOptions(betAttrValue)|raw }}
                                    </select>
                                </div>
                            </div>

                        {% endfor %}

                        <input type="hidden" name="event-id" value="{{ event.getId }}">
                        <input type="hidden" name="token" value="{{ token }}">

                        <!-- Button -->
                        <div class="center-block" style="padding: 15px">
                            <button id="event-bet-submit-{{ event.getId() }}"
                                    class="btn btn-new center-block submit" disabled="disabled">
                                {{ language.get('admin_result_upload_submit_' ~ event.getType()) }}
                            </button>
                        </div>
                    </fieldset>
                </form>
    {% endif %}
</div>
<script type="text/javascript">
    $(document).ready(function () {
        checkFakeBet('.event-{{ event.getId() }}', '#event-bet-submit-{{ event.getId() }}');
    });
</script>
