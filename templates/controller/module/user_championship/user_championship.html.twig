{% extends 'controller/module/base_module.tpl' %} {% block body_content %}
    <div class="panel-body center-block">
        <div class="table-responsive center-block">
            {% if resultsCount %}
                {% if processing %}
                    <table class="table table-striped">
                        <tr>
                            <td class="text-center">{{ language.get('general_processing') }}</td>
                            <td class="text-center"><img src="/src/view/image/15.gif"></td>
                        </tr>
                    </table>
                {% else %}
                    <table class="table table-striped">
                        <tbody>
                        {% for user in users %}
                            {% set podiumTrophies = user.getPodiumTrophies() %}
                            <tr>
                                <td class="text-center user-activity user-{{ user.getName() }}">
                            <span class="glyphicon glyphicon-eye-open"
                                  aria-hidden="true" title="Online"
                                  style="color: transparent; font-size: 1.1em;">
                            </span>
                                </td>
                                <td><strong>{{ user.getName() }}</strong></td>
                                <td>
                                    {% for type, trophies in podiumTrophies %}
                                        {% set trophy = trophies|first %}
                                        {% if trophy.getType() %}
                                            <span title="{{ language.get('trophy_type_' ~ trophy.getType()) }}">
                                    <img src="{{ domain }}/src/view/image/trophy_{{ trophy.getType() }}.png?v=1502019288"
                                         height="18">
                                </span>
                                            {% if trophies|length > 1 %}
                                                <span class="small">x{{ trophies|length }}</span>
                                            {% endif %}
                                        {% endif %}
                                    {% endfor %}
                                </td>
                                <td><strong class="color-one">{{ user.getPoint() }}</strong></td>
                                <td><span class="small color-two">{{ user.getPointDifference() }}</span></td>
                            </tr>
                        {% endfor %}
                        </tbody>
                    </table>
                    <table class="table table-condensed">
                        <tbody>
                        {% for typeKey, recordType in recordTypes %}
                            {% if recordType is not empty %}
                                <tr>
                                    <td class="text-center bg-one" colspan="3"
                                        style="border-top: 1px; border-top-color: white;">
                                        <strong class="color-two">{{ language.get(id ~ '_best_of_' ~ typeKey) }}</strong>
                                    </td>
                                </tr>
                                {% for record in recordType %}
                                    <tr class="text-center">
                                        <td><strong>{{ record.getUserName() }}</strong></td>
                                        <td><strong class="color-one">{{ record.getPoint() }}</strong></td>
                                        <td>
                                            <strong class="color-two">{{ record.getTimes() > 1 ? ' X' ~ record.getTimes() : '' }}</strong>
                                        </td>
                                    </tr>
                                {% endfor %}
                            {% endif %}
                        {% endfor %}
                        </tbody>
                    </table>
                {% endif %}
            {% else %}
                <table class="table table-striped">
                    <tr>
                        <td colspan="3" class="text-center">
                            <strong class="color-one">{{ language.get(id ~ '_no_results') }}</strong>
                        </td>
                    </tr>
                </table>
            {% endif %}
            <input type="hidden" id="results-count" value="{{ resultsCount }}">
        </div>
    </div>
{% endblock %}
