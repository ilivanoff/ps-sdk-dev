PsLocalStore.CLIENT = PsLocalStore.inst('client');


//(function($) {
/*
 * =====================
 * Идентифицируемые окна
 * =====================
 */
var PsIdentPagesManager = {
    pages: {},       //Зарегистрированные страницы
    page: null,      //Текущая страница
    processors: new ObjectsStore(),  //Зарегистрированные процессоры для страницы
    showStack: [],   //Стек открытия страниц
    dflt: 'content', //Id дефолтной страницы, на месте которой показываются загружаемые
    logger: PsLogger.inst('PsIdentPagesManager').setDebug()/*.disable()*/,
    hasPage: function (ident) {
        return this.pages.hasOwnProperty(ident);
    },
    isDflt: function (ident) {
        return this.dflt == ident;
    },
    isCurrent: function (ident) {
        return this.page ? this.page.equals(ident) : ident == this.dflt;
    },
    register: function (processor) {
        this.logger.logInfo('Зарегистирован процессор: ' + PsObjects.keys2array(processor));
        for (var ident in processor) {
            this.processors.putToArray(ident, processor[ident]);
        }
    },
    init: function () {

        var openers = {};
        $('a.ip-opener').each(function () {
            var $a = $(this);
            var ident = getHrefAnchor($a);
            var title = $a.attr('title');

            //Определим cover src, так как его больше неоткуда взять
            var $img = $a.children('img');
            var src = $img.isEmptySet() ? $a.backgroundImageUrl() : $img.attr('src');

            openers[ident] = {
                ident: ident,
                title: title,
                src: src
            };
            $a.clickClbck(function () {
                PsIdentPagesManager.openPage(ident, title);
                //PsIdentPagesManager.openPage(pageIdent, pageTitle, 'Search!');
            });
        });

        PsHotKeysManager.addListener('Ctrl+Alt+W', {
            f: function () {
                PsDialog.register({
                    id: 'IdentPagesDialog',
                    build: function (DIALOG, whenDone) {
                        $.each(openers, function (ident, ob) {
                            var $img = crIMG(ob.src, ob.ident);
                            var $a = crA().append($img).append(ob.title).
                                    clickClbck(function () {
                                        DIALOG.close();
                                        PsIdentPagesManager.openPage(ob.ident, ob.title);
                                    });
                            DIALOG.div.append($('<div>').append($a));
                        });
                        whenDone(DIALOG);
                    },
                    wnd: {
                        title: 'Загружаемые окна',
                        width: null,
                        minWidth: 300
                    }
                }).toggle();
            },
            descr: 'Загружаемые окна'
        });

        PsHotKeysManager.addListener('Ctrl+Alt+M', {
            f: function () {
                var ident = 'sitemap';
                var ob = openers[ident];
                if (PsIdentPagesManager.isCurrent(ident)) {
                    if (PsScroll.isScrolling()) {
                        //Мы сейчас выполняем скроллинг - пропускаем.
                        return;//---
                    }
                    PsIdentPagesManager.hideAll();
                    PsScrollManager.restoreWndScroll();
                } else {
                    PsScrollManager.storeWndScroll();
                    PsIdentPagesManager.openPage(ob.ident, ob.title);
                }
            },
            descr: 'Карта сайта'
        });

        //Регистрируем обработчик клика по ссылке внутри идентифицируемой страницы
        //Так как при повешании $(item).on - item уже должен быть добавлен на страницу, мы добавим слушатель на body
        PsJquery.on({
            ctxt: this,
            item: '.ps-ipage-content a[href]',
            click: this.processIpHrefClick
        });

        /*
         * Во время инициализации мы восстанавливаем предыдущую открытую страницу.
         * Сохраняем предыдущую страницу в тот момет, когда пользователь нажал F5.
         */
        PsHotKeysManager.addListener('F5', {
            ctxt: this,
            f: this.storeState
        });

        //Восстанавливаем состояние
        this.restoreState();
    },
    storeState: function () {
        //Если текущая страница - не загружаемая, или она была показана с ошибкой - не сохраняем состояние
        var curpage = this.page;
        if (!curpage || !curpage.data.ok)
            return;//--

        PsLocalStore.CLIENT.set('last_ident_page', {
            ident: curpage.ident,
            title: curpage.title
        });
    },
    restoreState: function () {
        var lastPage = PsLocalStore.CLIENT.get('last_ident_page');
        if (!lastPage)
            return;//---
        //Сразу после восстановления страницы - стираем информацию о ней.
        PsLocalStore.CLIENT.remove('last_ident_page');
        //Стартуем отложенный режим
        PsUtil.scheduleDeferred(function () {
            /*
             * Открываем страницу в отложенном режиме, так как сначала должны 
             * зарегистироваться плагины.
             * 
             * Тут мы убиваем двух зайцев сразу - если, например,
             * была открыта страница ЛК, а теперь пользователь разлогинен,
             * то мы не будем пытаться повторно открыть эту страницу, так как
             * для неё обработчик уже не будет зарегистрирован.
             */
            if (!this.processors.has(lastPage.ident))
                return;//---
            this.openPage(lastPage.ident, lastPage.title);
        }, this);
    },
    openPage: function (ident, title, force) {
        if (this.isDflt(ident)) {
            this.showStack.push({
                ident: ident,
                force: false
            });
            this.doShow();
            return;//---
        }

        if (!this.hasPage(ident)) {
            var logger = this.logger;
            var processors = this.processors.get(ident);

            if (!processors) {
                InfoBox.popupError("Не зарегистрирован обработчик для '" + title + "' (" + ident + ")");
                return;//---
            }

            logger.logInfo('Регистрируем страницу {}.', ident);

            this.pages[ident] = {
                ident: ident,
                title: title,
                adds: 0,
                shows: 0,
                div: null,
                /*
                 * Признак принудительной перезагрузки.
                 * Если пришла команда на перезагрузку страницы, но страница сейчас не открыта,
                 * то мы должны принудительно перезагрузить её при следующем открытии.
                 */
                reload: false,
                //Данные, устанавливаемые после загрузки содержимого страницы
                data: {
                    ctt: null,
                    jsp: null,
                    ok: null
                },
                //Некоторые страницы сами умеют грузить данные
                load: function (onLoadDone) {
                    var loaded = false;
                    processors.walk(function (processor) {
                        if (loaded)
                            return; //Уже загружаем ---
                        if (!$.isFunction(processor.load))
                            return;//Данный процессор не умеет загружать страницу---
                        loaded = true;
                        processor.load.call(processor, onLoadDone);
                    });
                    return loaded;
                },
                //Есть возможность отработать на события добавления/до-показа/после_показа
                fire: function (eventName) {
                    logger.logTrace('Поступило событие {}->{}.', eventName, ident);
                    processors.walk(function (processor) {
                        var method = processor['on' + eventName.firstCharToUpper()];
                        if (!$.isFunction(method))
                            return;//Процессор не случает данное событие
                        logger.logDebug('Отравляем событие {}->{}.', eventName, ident);
                        //При вызове метода мы передадим не все параметры, а только некоторые
                        method.call(processor, {
                            ident: this.ident,
                            adds: this.adds,
                            shows: this.shows,
                            div: this.div,
                            js: this.data.jsp
                        });
                    }, false, this);
                },
                //Аналог equals
                equals: function (other) {
                    if (PsIs.string(other))
                        return this.ident == other;
                    if (PsIs.object(other))
                        return this.ident == other.ident;
                    return false;
                }
            }
        }

        var page = this.pages[ident];

        this.showStack.push({
            ident: ident,
            force: force || page.reload
        });

        //Сбросим признак принудительной перезагрузки
        page.reload = false;

        this.doShow();
    },
    openDefaultPage: function () {
        this.openPage(this.dflt);
    },
    hideAll: function () {
        this.openDefaultPage();
    },
    resetPage: function (ident) {
        var page = this.pages[ident];
        this.logger.logInfo('Поступил запрос на сброс страницы [{}]', ident);
        if (!page || page.data.ok === false)
            return; //Если страницы ещё нет или она загрузилась с ошибкой - ничего не делаем
        //Установим признак необходимости перезагрузки
        page.reload = true;
    },
    reloadPage: function (ident) {
        var page = this.pages[ident];
        this.logger.logInfo('Поступил запрос на перезагрузку страницы [{}]', ident);
        if (!page || page.data.ok === false)
            return; //Если страницы ещё нет или она загрузилась с ошибкой - ничего не делаем
        //Установим признак необходимости перезагрузки
        page.reload = true;
        //Если сейчас открыта обновлённая страница - откроем её заново
        if (this.isCurrent(page)) {
            this.openPage(ident, page.title);
        }
    },
    inProgress: false,
    doShow: function () {
        if (this.inProgress || this.showStack.length == 0)
            return;//---
        var logger = this.logger;

        var rules = this.showStack.shift();
        var ident = rules.ident;
        var force = rules.force;
        var page = this.pages[ident];

        //У дефолтной страницы нет объекта page, на этом основана логика.
        if (page && !force && this.isCurrent(page)) {
            logger.logTrace('Страница {} является текущей.', ident);
            //this.openDefaultPage();
            return;//---
        }

        logger.logDebug('Открываем страницу {}.', ident);

        this.inProgress = true;
        this.closerHide();

        $('#leftPanel').children().hide();

        if (!page) {
            this.doShowImpl(null);
            return;//---
        }

        //Если страница однажды загрузилась некорректно, больше её не перезагружаем
        if (page.data.ok === false) {
            this.doShowImpl(page);
            return;//---
        }

        //Страница уже добавлена и перезагружать её не нужно
        if (page.adds > 0 && !force) {
            this.doShowImpl(page);
            return;//---
        }

        var secundomer = new PsSecundomer(true);
        logger.logInfo('Загружаем страницу {}...', ident);

        //Загружаем/перезагружаем страницу
        var onLoadDone = PsUtil.once(function (ctt, jsp, error) {
            logger.logInfo('Страница {} загружена за {} секунд.', ident, secundomer.stop());
            this.onPageLoaded(page, ctt, jsp, error);
            this.progressHide();
            this.doShowImpl(page);
        }, this);

        if (page.div)
            page.div.remove();

        this.progressShow(page.title);

        try {
            if (page.load(onLoadDone)) {
                //Страница самостоятельно загрузила данные...
                logger.logInfo('Страница {} самостоятельно загрузила данные.', ident);
                return;//---
            }
        } catch (e) {
            //Мы пытались загрузить содержимое и получили ошибку
            logger.logError('Эксепшн во время загрузки страницы {}: {}.', ident, e);
            onLoadDone(e, null, true);
            return;//---
        }

        //Страница не грузит данные сама для себя, полезем за данными на сервер.
        logger.logInfo('Страница {} будет загружена с сервера.', ident);

        var request = {};
        request[defs.IDENT_PAGE_PARAM] = ident;
        request.ctxt = this;

        AjaxExecutor.execute('IdentPages',
                request,
                function (resp) {
                    onLoadDone(resp['ctt'], resp['jsp'], false);
                },
                function (err) {
                    onLoadDone(InfoBox.divError(err), null, true);
                });
    },
    onPageLoaded: function (page, ctt, jsp, error) {
        page.data = {
            ctt: ctt,
            jsp: jsp,
            ok: !error
        }
        //Наша задача обернуть возвращённое содержимое в див. Если вернулся див, то он и должен быть использован.

        var $div = $('<div>').append(ctt);
        var $divChild = $div.children();

        page.div = $divChild.size() == 1 && $divChild.is('div') ? $divChild : $div;
        page.div.addClass('ps-ipage-content').hide().appendTo('#leftPanel');

        if (error)
            return;//---

        ++page.adds;
        page.shows = 1;
        page.fire('Add');
    },
    /**
     * Метод показывает страницу, при этом:
     * 1. Если page==null, то это - дефолтная страница
     * 2. Если page!=null, то это - успешно загруженная не дефолтная страница
     */
    doShowImpl: function (page) {
        var ident = page ? page.ident : this.dflt;

        if (page && page.data.ok) {
            page.fire('BeforeShow');
        }

        if (page) {
            page.div.show();
        } else {
            //Показываем див дефолтной страницы
            $('#leftPanel>#' + ident).show();
        }

        if (page && page.data.ok) {
            page.fire('AfterShow');
            ++page.shows;
        }

        if (page) {
            this.closerShow();
        }

        this.page = page;
        this.inProgress = false;
        this.doShow();
    },
    closer: null,
    closerHide: function () {
        if (this.closer) {
            this.closer.remove();
            this.closer = null;
        }
    },
    closerShow: function () {
        this.closerHide();

        var _this = this;
        this.closer = crCloser(function () {
            _this.hideAll();
        }, 'ipcloser').prependTo('#leftPanel');
    },
    progress: null,
    progressHide: function () {
        if (this.progress) {
            this.progress.remove();
            this.progress = null;
        }
    },
    progressShow: function (title) {
        this.progressHide();
        this.progress = loadingMessageDiv(title).appendTo('#leftPanel');
    },
    /*
     * Обработаем клик по ссылке на идентифицируемой странице.
     * Возможно наша задача сведётся к тому, чтобы просто её скрыть.
     * Скрывать будем, если ссылка является обычной (есть что-то помимо якоря) 
     * и указывает НА ЭТУ ЖЕ страницу.
     * 
     * Возвращаем true, если мы обработали эту ссылку, иначе - false и ссылка будет обработана
     * стандартным образом.
     */
    processIpHrefClick: function (event, $a) {
        if (!PsUrl.isUsualHref($a))
            return false;// Ссылка только с якорем
        if (PsUrl.isUsualHref2AnotherPage($a))
            return false;// Обычная ссылка, но на другую страницу

        //По клику мы должны остаться на этой странице
        var hasAnchor = !!getHrefAnchor($a);
        PsIdentPagesManager.hideAll();//this
        if (hasAnchor) {
            //Переходим по якорю
        } else {
            //Сбросим якорь и покажем страницу под идентифицируемой
            event.preventDefault();
            if (window.location.hash) {
                window.location.hash = '';
            }
            PsScroll.jumpTop();
        }
        return true;
    }
}