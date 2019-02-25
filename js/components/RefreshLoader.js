'use strict';

import { VisualComponent } from "./VisualComponent";

const classes = {
    SPIN: 'fa-spin',
    SUCCESS: 'text-success',
    WAITING: 'text-warning',
    ERROR: 'text-danger',
    HIDDEN: 'd-none',
};

class RefreshLoader extends VisualComponent
{
    static get event() {
        return {
            CLICK: 'click',
        }
    }

    static get state() {
        return {
            WAITING: 'waiting',
            SUCCESS: 'success',
            ERROR: 'error',
            HIDDEN: 'hidden',
        };
    }

    /**
     * @param {jQuery|string} container
     * @param {jQuery|string} lockContainer
     * @param {string} [state]
     */
    constructor(container, lockContainer, state)
    {
        super(container);
        this.lockContainer = $(lockContainer);
        this.state = state || RefreshLoader.state.SUCCESS;
        this.icon = this.container.find('i');

        this.initEvents();
    }

    /**
     * @param {jQuery|string} container
     */
    setContainer(container)
    {
        this.container = $(container);
        this.icon = this.container.find('i');
    }

    /**
     * @param {jQuery|string} lockContainer
     */
    setLockContainer(lockContainer)
    {
        this.lockContainer = $(lockContainer);
    }

    /**
     * @param {string} state
     */
    setState(state)
    {
        if (state === RefreshLoader.state.WAITING) {
            this.lock(this.lockContainer);
        } else {
            this.unlock(this.lockContainer);
        }

        this.icon.removeClass(classes.SPIN + ' ' + classes.SUCCESS + ' ' + classes.WAITING + ' ' + classes.ERROR + ' ' + classes.HIDDEN);

        switch (state) {
            case RefreshLoader.state.SUCCESS: this.icon.addClass(classes.SUCCESS); break;
            case RefreshLoader.state.WAITING: this.icon.addClass(classes.WAITING + ' ' + classes.SPIN); break;
            case RefreshLoader.state.HIDDEN: this.icon.addClass(classes.HIDDEN); break;            
            default: this.icon.addClass(classes.ERROR);
        }
    }

    initEvents()
    {
        let self = this;
        this.container.on('click', function() { self.trigger(RefreshLoader.event.CLICK); });
    }
}

export { RefreshLoader }