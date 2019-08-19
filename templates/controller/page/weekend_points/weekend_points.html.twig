<div class="panel panel-default">
    <div class="panel-heading text-center">
        <strong>{{ language.get(id~'_title') }}</strong>
    </div>
    <table class="table table-striped">
        <thead>
        <tr>
            <th><strong class="color-two">{{ language.get(id ~ '_name') }}</strong></th>
            <th><strong class="color-two">{{ language.get(id ~ '_points') }}</strong></th>
            <th><strong class="color-two">{{ language.get(id ~ '_lag') }}</strong></th>
        </tr>
        </thead>
        <tbody>
        {% set maxPoint = (usersPoints|first).getPoint %}
        {% for userPoint in usersPoints %}
            <tr>
                <td>
                    <strong class="color-one">{{ userPoint.getUser.getName }}</strong>
                </td>
                <td>
                    <strong class="color-one">{{ userPoint.getPoint }}</strong>
                </td>
                <td>
                    {% if maxPoint - userPoint.getPoint %}
                        <span class="small color-two">+{{ maxPoint - userPoint.getPoint }}</span>
                    {% endif %}
                </td>
            </tr>
        {% endfor %}
        {% if usersPoints is empty %}
            <tr>
                <td colspan="3" class="text-center">
                    <strong class="color-one">{{ language.get(id ~ '_no_results') }}</strong>
                </td>
            </tr>
        {% endif %}
        <tr>
            <td colspan="3" class="text-center" style="padding: 15px;">
                <strong class="color-two">{{ language.get(id ~ '_description') }}</strong>
                <br><br><strong class="color-two">{{ language.get(id ~ '_goal') }}</strong>
                <br><br><strong class="color-three">{{ language.get(id ~ '_test') }}</strong>
            </td>
        </tr>
        </tbody>
    </table>
    <div class="panel panel-default">
        <div class="panel-heading text-center">
            <strong>{{ language.get(id ~ '_point_calculating') }}</strong>
        </div>
        <table class="table table-striped table-responsive">
            <thead>
            <tr>
                <th><strong class="color-two">{{ language.get(id ~ '_place') }}</strong></th>
                {% for place, point in pointProvider.getPlacePoints %}
                    <th class="text-center"><strong class="color-two">{{ place }}.</strong></th>
                {% endfor %}
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>
                    <strong class="color-one">{{ language.get(id ~ '_converted_points') }}</strong>
                </td>
                {% for place, point in pointProvider.getPlacePoints %}
                    <td class="text-center"><strong class="color-one">{{ point }}</strong></td>
                {% endfor %}
            </tr>
            </tbody>
        </table>
    </div>
    {% for weekend in weekends %}
        <div class="table-responsive">
            <div class="panel panel-default">
                <div class="panel-heading text-center">
                    <strong>{{ weekend.getEvent.getName }}</strong>
                </div>
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th><strong class="color-two">{{ language.get(id ~ '_name') }}</strong></th>
                        <th><strong class="color-two">{{ language.get(id ~ '_collected_points') }}</strong></th>
                        <th><strong class="color-two">{{ language.get(id ~ '_converted_points') }}</strong></th>
                    </tr>
                    </thead>
                    <tbody>
                    {% for userPoint in weekend.usersPoints %}
                        <tr>
                            <td>
                                <strong>{{ userPoint.getUser.getName }}</strong>
                            </td>
                            <td>
                                <strong>{{ userPoint.getCollectedPoint }}</strong>
                            </td>
                            <td>
                                <strong class="color-one">{{ userPoint.getConvertedPoint }}</strong>
                            </td>
                        </tr>
                    {% endfor %}
                    </tbody>
                </table>
            </div>
        </div>
    {% endfor %}
</div>
