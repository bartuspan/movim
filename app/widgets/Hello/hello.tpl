<div id="hello_widget" class="divided">
    <ul class="flex active middle">
        <li class="subheader block large">{$c->__('hello.active_contacts')}</li>
        {loop="$top"}
            <li tabindex="{$key+1}" class="block action {if="$value->status"}condensed{/if}"
                onclick="Hello_ajaxChat('{$value->jid}')">
                {$url = $value->getPhoto('s')}
                {if="$url"}
                    <span
                        class="icon bubble
                        {if="$value->value"}
                            status {$presencestxt[$value->value]}
                        {/if}">
                        <img src="{$url}">
                    </span>
                {else}
                    <span
                        class="icon bubble color {$value->jid|stringToColor}
                        {if="$value->value"}
                            status {$presencestxt[$value->value]}
                        {/if}">
                        <i class="zmdi zmdi-account"></i>
                    </span>
                {/if}

                <span>{$value->getTrueName()}</span>
                <p class="wrap">{$value->status}</p>
            </li>
        {/loop}
        <a class="block large" href="{$c->route('chat')}">
            <li class="action">
                <div class="action">
                    <i class="zmdi zmdi-chevron-right"></i>
                </div>
                <span class="icon">
                    <i class="zmdi zmdi-comments"></i>
                </span>
                <span>{$c->__('hello.chat')}</span>
            </li>
        </a>
    </ul>
    {if="$c->supported('pubsub')"}
        <ul id="news" class="flex thick active">
            <li class="subheader block large">{$c->__('hello.news')}</li>
            {loop="$news"}
                <li class="block condensed"
                    data-id="{$value->nodeid}"
                    {if="$value->title != null"}
                        title="{$value->title|strip_tags}"
                    {else}
                        title="{$c->__('hello.contact_post')}"
                    {/if}
                    onclick="movim_reload('{$c->route('news', $value->nodeid)}')"
                >
                    {if="current(explode('.', $value->origin)) == 'nsfw'"}
                        <span class="icon bubble color red tiny">
                            +18
                        </span>
                    {elseif="$value->node == 'urn:xmpp:microblog:0'"}
                        {$url = $value->getContact()->getPhoto('s')}
                        {if="$url"}
                            <span class="icon bubble">
                                <img src="{$url}">
                            </span>
                        {else}
                            <span
                                class="icon bubble color {$value->getContact()->jid|stringToColor}">
                                <i class="zmdi zmdi-account"></i>
                            </span>
                        {/if}
                    {else}
                        <span class="icon bubble color {$value->node|stringToColor}">{$value->node|firstLetterCapitalize}</span>
                    {/if}

                    {if="$value->title != null"}
                        <span>{$value->title}</span>
                    {else}
                        <span>{$c->__('hello.contact_post')}</span>
                    {/if}

                    <span class="info">{$value->published|strtotime|prepareDate}</span>
                    
                    <p class="more">
                        {if="current(explode('.', $value->origin)) != 'nsfw'"}
                            {$value->contentcleaned|strip_tags:'<img><img/>'}
                        {/if}
                    </p>
                </li>
            {/loop}
            <a  class="block large" href="{$c->route('news')}">
                <li class="action">
                    <div class="action">
                        <i class="zmdi zmdi-chevron-right"></i>
                    </div>
                    <span class="icon">
                        <i class="zmdi zmdi-receipt"></i>
                    </span>
                    <span>{$c->__('hello.news_page')}</span>
                </li>
            </a>
        </ul>
        <br />
        <ul class="active thick on_desktop">
            <a href="{$c->route('blog', array($jid))}" target="_blank">
                <li class="condensed action">
                    <div class="action">
                        <i class="zmdi zmdi-chevron-right"></i>
                    </div>
                    <span class="icon">
                        <i class="zmdi zmdi-portable-wifi"></i>
                    </span>
                    <span>{$c->__('hello.blog_title')}</span>
                    <p>{$c->__('hello.blog_text')}</p>
                </li>
                <br/>
            </a>
        </ul>
        <ul class="thick flex on_desktop">
            <li class="condensed block">
                <span class="icon bubble color blue">
                    <i class="zmdi zmdi-share"></i>
                </span>
                <span>{$c->__('hello.share_title')}</span>
                <p>{$c->__('hello.share_text')}</p>
            </li>
            <li class="block">
                <a class="button" href="javascript:(function(){location.href='{$c->route('share')}&url='+encodeURIComponent(location.href);})();">
                    <i class="zmdi zmdi-share"></i> {$c->__('hello.share_button')}
                </a>
            </li>
        </ul>
    {/if}
</div>
