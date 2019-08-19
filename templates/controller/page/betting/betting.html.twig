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

{% if user %}
    <div class="row">
        {% for event in events %}
            <div class="col-sm-6">
                <div class="panel panel-default text-center">
                    <div class="panel-heading text-center">
                        <strong>{{ event.event.getName() }}
                            - {{ language.get('general_' ~ event.event.getType) }}</strong>
                    </div>
                    <form class="form-horizontal" method="post" action="?page=betting/save">
                        <fieldset style="margin-top: 10px;">

                            {% if event.inTime or event.bet %}
                                <table class="table table-responsive table-striped">
                                    <!-- Select Basic -->
                                    {% for eventAttribute in event.eventAttributes %}
                                        <tr>
                                            <td>

                                                {% set attrId = eventAttribute.getId() %}
                                                {% set attrType = eventAttribute.getType() %}
                                                {% set betAttrValue = event.bet ? event.bet.getAttributeValueByKey(attrId) : '' %}

                                                <div class="form-group">
                                                    <label class="col-md-4 control-label"
                                                           for="selectbasic">{{ language.get('betting_' ~ attrId) }}</label>
                                                    <div class="col-md-7">
                                                        <select id="selectbasic" name="bet_attr[{{ attrId }}]"
                                                                class="form-control event-{{ event.event.getId() }}"
                                                                {{ event.bet ? ' disabled="disabled"' : '' }}>
                                                            <option value="">{{ language.get('betting_default_option') }}</option>
                                                            {{ formHelper.getSelectOption(attrType).getOptions(betAttrValue)|raw }}
                                                        </select>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    {% endfor %}
                                </table>

                                <input type="hidden" name="user-token" value="{{ userToken }}">
                                <input type="hidden" name="event-id" value="{{ event.event.getId }}">
                                <input type="hidden" name="token" value="{{ token }}">

                                <!-- Button -->
                                {% if not event.bet %}
                                    <div class="center-block" style="padding: 15px">
                                        <button id="event-bet-submit-{{ event.event.getId() }}"
                                                class="btn btn-new center-block" disabled="disabled">
                                            {{ language.get('betting_submit_' ~ event.event.getType()) }}
                                        </button>
                                    </div>
                                {% endif %}

                            {% else %}
                                <div class="text-center text-danger"><h4 class="color-two">{{ language.get('betting_time_out') }}</h4>
                                </div>
                            {% endif %}
                        </fieldset>
                    </form>
                </div>
            </div>
        {% endfor %}
    </div>
{% else %}
    <h3 class="color-two">{{ language.get('betting_no_login') }}</h3>
{% endif %}

<script type="text/javascript">
    $(document).ready(function () {
        {% for event in events %}
        checkFakeBet('.event-{{ event.event.getId() }}', '#event-bet-submit-{{ event.event.getId() }}');
        {% endfor %}
    });
</script>
