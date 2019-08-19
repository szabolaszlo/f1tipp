<form id="message-send" class="form-inline" method="post" action="?module=message_wall/save&ajax=true">
    <input type="text" class="form-group smile-replaceable form-input" name="message" placeholder="{{ placeholder }}"
           style="width: 65%">
    <button id="message-submit" type="submit"
            class="btn btn-new form-group">{{ language.get(id ~ '_submit') }}
    </button>
    <img id="loading" src="/src/view/image/ajax-loader.gif" height="34" style="display: none;">
    <input type="hidden" name="user-token" value="{{ userToken }}">
    <input type="hidden" name="token" value="{{ token }}">
</form>
<script>
    var f1tippSmileys = {
        'o/': '👋',
        '</3': '💔',
        '<3': '💗',
        '8-D': '😁',
        '8D': '😁',
        ':-D': '😁',
        '=-3': '😁',
        '=-D': '😁',
        '=3': '😁',
        '=D': '😁',
        'B^D': '😁',
        'X-D': '😁',
        'XD': '😁',
        'x-D': '😁',
        'xD': '😁',
        ':\')': '😂',
        ':\'-)': '😂',
        ':-))': '😃',
        '8)': '😄',
        ':)': '😄',
        ':-)': '😄',
        ':3': '😄',
        ':D': '😄',
        ':]': '😄',
        ':^)': '😄',
        ':c)': '😄',
        ':o)': '😄',
        ':}': '😄',
        ':っ)': '😄',
        '=)': '😄',
        '=]': '😄',
        '0:)': '😇',
        '0:-)': '😇',
        '0:-3': '😇',
        '0:3': '😇',
        '0;^)': '😇',
        'O:-)': '😇',
        '3:)': '😈',
        '3:-)': '😈',
        '}:)': '😈',
        '}:-)': '😈',
        '*)': '😉',
        '*-)': '😉',
        ':-,': '😉',
        ';)': '😉',
        ';-)': '😉',
        ';-]': '😉',
        ';D': '😉',
        ';]': '😉',
        ';^)': '😉',
        ':-|': '😐',
        ':|': '😐',
        ':(': '😒',
        ':-(': '😒',
        ':-<': '😒',
        ':-[': '😒',
        ':-c': '😒',
        ':<': '😒',
        ':[': '😒',
        ':c': '😒',
        ':っC': '😒',
        '%)': '😖',
        '%-)': '😖',
        ':-P': '😜',
        ':-b': '😜',
        ':-p': '😜',
        ':-Þ': '😜',
        ':-þ': '😜',
        ':P': '😜',
        ':b': '😜',
        ':p': '😜',
        ':Þ': '😜',
        ':þ': '😜',
        ';(': '😜',
        '=p': '😜',
        'X-P': '😜',
        'XP': '😜',
        'd:': '😜',
        'x-p': '😜',
        'xp': '😜',
        ':-||': '😠',
        ':@': '😠',
        ':-.': '😡',
        ':-/': '😡',
        ':/': '😡',
        ':L': '😡',
        ':S': '😡',
        ':\\': '😡',
        '=/': '😡',
        '=L': '😡',
        '=\\': '😡',
        ':\'(': '😢',
        ':\'-(': '😢',
        '^5': '😤',
        '^<_<': '😤',
        'o/\\o': '😤',
        '|-O': '😫',
        '|;-)': '😫',
        ':###..': '😰',
        ':-###..': '😰',
        'D-\':': '😱',
        'D8': '😱',
        'D:': '😱',
        'D:<': '😱',
        'D;': '😱',
        'D=': '😱',
        'DX': '😱',
        'v.v': '😱',
        '8-0': '😲',
        ':-O': '😲',
        ':-o': '😲',
        ':O': '😲',
        ':o': '😲',
        'O-O': '😲',
        'O_O': '😲',
        'O_o': '😲',
        'o-o': '😲',
        'o_O': '😲',
        'o_o': '😲',
        ':$': '😳',
        '#-)': '😵',
        ':#': '😶',
        ':&': '😶',
        ':-#': '😶',
        ':-&': '😶',
        ':-X': '😶',
        ':X': '😶',
        ':-J': '😼',
        ':*': '😽',
        ':^*': '😽',
        'ಠ_ಠ': '🙅',
        '*\\0/*': '🙆',
        '\\o/': '🙆',
        ':>': '😄',
        '>.<': '😡',
        '>:(': '😠',
        '>:)': '😈',
        '>:-)': '😈',
        '>:/': '😡',
        '>:O': '😲',
        '>:P': '😜',
        '>:[': '😒',
        '>:\\': '😡',
        '>;)': '😈',
        '>_>^': '😤'
    };
    $(document).ready(function () {
        $('#message-submit').click(function () {
            $('#loading').show();
            $('#message-submit').hide();
        });

        $('#message-send').ajaxForm(function () {
            $('#messages-list').load('?module=message_wall/messages&ajax=true');
            $('#messages-send-form-div').load('?module=message_wall/form&ajax=true');
        });

        setInterval(function () {
            $('#messages-list').load('?module=message_wall/messages&ajax=true');
        }, 15000);

        $('.smile-replaceable').keyup(function () {
            var words = $('.smile-replaceable').val().split(' ');
            var i;
            var replaced = false;
            for (i = 0; i < words.length; ++i) {
                if (f1tippSmileys[words[i]]) {
                    replaced = true;
                    words[i] = f1tippSmileys[words[i]];
                }
            }
            if (replaced) {
                $('.smile-replaceable').val(words.join(" "));
            }
        });
    });
</script>