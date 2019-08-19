{% extends 'system/resulttable/type/full.tpl' %}
{% block heading_title %}
    <strong>{{ event.getName() }} - {{ language.get('general_' ~ event.getType) }}</strong>
{% endblock %}
{% block body_content %}
    <div class="table-responsive">
        <table class="table table-striped">
            {% set betAttributes = bets|first.getAttributes() %}
            <thead>
            <tr>
                <th><strong class="color-two">{{ language.get('result_name') }}</strong></th>
                {% for betAttribute in betAttributes %}
                    <th class="text-center"><strong class="color-two">{{ language.get('result_' ~ betAttribute.getKey()) }}</strong></th>
                {% endfor %}
            </tr>
            </thead>
            <tbody>
            {% for bet in bets %}
                <tr>
                    <td class="text-left"><strong class="color-one">{{ bet.getUser().getName() }}</strong></td>
                    {% for betAttribute in bet.getAttributes() %}
                        <td class="text-center"><strong>{{ betAttribute.getValue() }}</strong></td>
                    {% endfor %}
                </tr>
            {% endfor %}
            </tbody>
        </table>
    </div>
    <input type="hidden" id="event-table-bet-numbers" value="{{ bets|length }}">
    <input type="hidden" id="event-table-event-id" value="{{ event.getId() }}">
{% endblock %}