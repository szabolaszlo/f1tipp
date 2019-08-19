<div class="panel panel-default">
    <div class="panel-heading text-center">
        <strong>{{ language.get('nav_admin_information_editor') }}</strong>
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
        {% if informations %}
            <table class="table">
                <thead>
                <tr>
                    <th>{{ language.get('admin_information_editor_information_title') }}</th>
                    <th class="text-center">{{ language.get('admin_information_editor_news') }}</th>
                    <th class="text-center">{{ language.get('admin_information_editor_edit') }}</th>
                </tr>
                </thead>
                {% for information in informations %}
                    <tr>
                        <td>{{ information.getTitle() }}</td>
                        <td class="text-center">
                            {% if information.getNews() %}
                                <a href="?page=admin/information_editor/removeNews&information_id={{ information.getId() }}"
                                   class="text-right glyphicon glyphicon-eye-open remove"
                                   aria-hidden=true title="{{ language.get('admin_information_editor_edit') }}">
                                </a>
                            {% else %}
                                <a href="?page=admin/information_editor/addNews&information_id={{ information.getId() }}"
                                   class="text-right glyphicon glyphicon-eye-close remove"
                                   aria-hidden=true title="{{ language.get('admin_information_editor_edit') }}">
                                </a>
                            {% endif %}
                        </td>
                        <td class="text-center"><a href="?page=admin/information_editor/update&information_id={{ information.getId() }}"
                               class="text-right glyphicon glyphicon-pencil remove"
                               aria-hidden=true title="{{ language.get('admin_information_editor_edit') }}">
                            </a>
                        </td>

                    </tr>
                {% endfor %}
            </table>
        {% endif %}
        {% if information %}
            <form class="form-horizontal" style="padding: 30px;" method="post"
                  action="?page=admin/information_editor/update&information_id={{ information.getId() }}">
                <fieldset>
                    <label for="information-title">{{ language.get('admin_information_editor_information_title') }}</label>
                    <input name="information_title" type="text" class="form-control" id="information-title"
                           value="{{ information.getTitle() }}">
                    <p></p>
                    <label for="ck-editor">{{ language.get('admin_information_editor_information_content') }}</label>
                    <textarea name="information_content" id="ck-editor">{{ information.getContent() }}</textarea>
                    <p></p>
                    <div class="center-block">
                        <button id="event-bet-submit-{{ event.getId() }}"
                                class="btn btn-new center-block submit">
                            {{ language.get('admin_information_editor_save') }}
                        </button>
                    </div>
                </fieldset>
            </form>
        {% else %}
            <div class="center-block" style="padding: 15px;">
                <a href="?page=admin/information_editor/insert" style="text-decoration: none">
                    <button id="event-bet-submit-{{ event.getId() }}"
                            class="btn btn-new center-block submit">
                        {{ language.get('admin_information_editor_new') }}
                    </button>
                </a>
            </div>
        {% endif %}
    {% endif %}
</div>
<script src="/vendor/ckeditor/ckeditor/ckeditor.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        CKEDITOR.replace('ck-editor');
    });
</script>