{% block heading_title %}
    {% for information in informations %}
        <strong>{{ information.getTitle() }}{{ not loop.last ? ', ' : '' }}</strong>
    {% endfor %}
{% endblock %}
{% extends 'controller/module/base_module.tpl' %} {% block body_content %}
    {% for information in informations %}
        <div style="padding: 15px 15px 0px 15px">
            {{ information.getContent()|raw }}
        </div>
        <form class="form skip-this-news" style="padding: 0px 15px 15px 15px" method="post"
              action="?module=news/addSkippedNews&ajax=true">
            <button type="submit"
                    class="btn btn-new form-group center-block submit-skip-news">{{ language.get('news_submit') }}</button>
            <input type="hidden" name="user-token" value="{{ userToken }}">
            <input type="hidden" name="information-id" value="{{ information.getId() }}">
        </form>
    {% endfor %}
    <script>
        $(document).ready(function () {
            $('.submit-skip-news').click(function () {
                $('.remove-able-news').hide();
            });

            $('.skip-this-news').ajaxForm();
        });
    </script>
{% endblock %}