'use strict';

import { EventManager } from "../libraries/EventManager";

const config = {
    lockedClass: 'box-spinner'
};

class VisualComponent
{
    /**
     * @param {jQuery|string} container
     */
    constructor(container)
    {
        this.container = $(container);
        this._locked = 0;

        /** @member {EventManager} */
        this.eventManager = new EventManager();
    }

    /**
     * @param {jQuery} [element]
     */
    lock(element)
    {
        if (element instanceof jQuery) {
            element.addClass(config.lockedClass);
            return;
        }

        this._locked++;
        this._refreshLock();
    };

    /**
     * @param {boolean|jQuery} [hard=false]
     */
    unlock(hard)
    {
        if (hard instanceof jQuery) {
            hard.removeClass(config.lockedClass);
            return;
        }

        if (this._locked < 1) {
            return;
        }

        if (hard === undefined) {
            hard = false;
        }

        if (hard === true) {
            this._locked = 0;
        } else {
            this._locked--;
        }

        this._refreshLock();
    };

    /**
     * @return {boolean}
     */
    checkLock()
    {
        return this._locked === 0;
    };

    _refreshLock()
    {
        if (this.container instanceof jQuery) {
            if (this._locked === 0) {
                this.container.removeClass(config.lockedClass);
            } else {
                this.container.addClass(config.lockedClass);
            }
        }
    };

    /**
     * @param {string} event
     * @param {*} [data]
     */
    trigger(event, data)
    {
        this.eventManager.trigger(event, data, this);
    };

    /**
     * @param {string} event
     * @param {*} [data]
     */
    globalTrigger(event, data)
    {
        EventManager.global.trigger(event, data, this);
    };

    /**
     * @param {string} event
     * @param {function} callback
     * @returns {CallbackHandler}
     */
    on(event, callback)
    {
        return this.eventManager.listen.apply(this.eventManager, arguments);
    };

    /**
     * @param {string} event
     * @param {function} callback
     * @returns {CallbackHandler}
     */
    static globalOn(event, callback)
    {
        return EventManager.global.listen(event, callback);
    };

    /**
     * @param {CallbackHandler} callbackHandler
     */
    off(callbackHandler)
    {
        this.eventManager.detach.apply(this.eventManager, arguments);
    };

    /**
     * @param {CallbackHandler} callbackHandler
     */
    static globalOff(callbackHandler)
    {
        EventManager.global.detach(callbackHandler);
    };
}

export { VisualComponent }