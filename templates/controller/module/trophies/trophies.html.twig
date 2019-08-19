{% extends 'controller/module/base_module.tpl' %}
{% block heading_title %}
    {% set event = title|length > 20 ? title|split(' - ').0 : title %}
    <strong>{{ event }} - {{ language.get('trophy_trophies') }}</strong>
{% endblock %}
{% block body_content %}
    <div class="panel-body center-block">
        <div class="table-responsive table-striped center-block">
            {% if processing %}
                <table class="table table-striped">
                    <tr>
                        <td class="text-center">{{ language.get('general_processing') }}</td>
                        <td class="text-center"><img src="/src/view/image/15.gif"></td>
                    </tr>
                </table>
            {% else %}
                <table class="table table-striped">
                    {% for type, trophies in podiumTrophies %}
                        <tr>
                            <td class="text-center">
                        <span title="{{ language.get('trophy_type_' ~ type) }}">
                            <img src="/src/view/image/trophy_{{ type }}.png?v=1502019288" height="18">
                        </span>
                            </td>
                            <td>
                                {% for trophy in trophies %}
                                    {% set user = trophy.getUser() %}
                                    {% set point = trophy.getPoint() %}
                                    <strong>{{ user.getName() }}{{ loop.last ? '' : ', ' }}</strong>
                                {% endfor %}
                            </td>
                            <td><strong>{{ trophies|first.getPoint() }}</strong></td>
                        </tr>
                    {% endfor %}
                </table>
            {% endif %}
            <input type="hidden" id="trophy-result-id" value="{{ eventId + 1 }}">
        </div>
    </div>
{% endblock %}
