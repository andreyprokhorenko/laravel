'use strict';

/**
 * @param {string} event
 * @param {number} callbackId
 * @constructor
 */
function CallbackHandler(event, callbackId)
{
    this.event = event;
    this.callbackId = callbackId;
}

/**
 * @param {string} eventType
 * @param {Object} target
 * @param {*} data
 * @param {number} counter
 * @constructor
 */
function Event(eventType, target, data, counter)
{
    this.eventType = eventType;
    this.target = target;
    this.data = data;
    this.counter = counter;
}

class EventManager
{
    constructor()
    {
        this.listeners = {};
        this.triggerCounter = {};
    }

    /**
     * @param {string} eventType
     * @param {Object} [data]
     * @param {Object} [target]
     * @returns {boolean|null}
     */
    trigger(eventType, data, target)
    {
        let event = new Event(
            eventType, target, data, this._addTriggerCounter(eventType)
        );

        if (
            (this.listeners.hasOwnProperty('*') === true) &&
            (eventType !== '*')
        ) {
            this._addTriggerCounter('*');
            EventManager._triggerProcess(this.listeners['*'], event);
        }

        if (this.listeners.hasOwnProperty(eventType) === false) {
            return null;
        }

        return EventManager._triggerProcess(this.listeners[eventType], event);
    };

    /**
     * @param {string} event
     * @param {function} callback
     * @returns {CallbackHandler}
     */
    listen(event, callback)
    {
        if (this.listeners.hasOwnProperty(event) === false) {
            this.listeners[event] = [];
        }

        let callbackId = this.listeners[event].push(callback);
        return new CallbackHandler(event, callbackId);
    };

    /**
     * @param {CallbackHandler} callbackHandler
     */
    detach(callbackHandler)
    {
        delete this.listeners[callbackHandler.event][callbackHandler.callbackId];
    };

    /**
     * @param {string} event
     * @return {number|boolean}
     */
    checkEvent(event)
    {
        return this.triggerCounter.hasOwnProperty(event) ? this.triggerCounter[event] : false;
    };

    /**
     * @param {string} event
     * @return {number}
     */
    _addTriggerCounter(event)
    {
        let tg = this.triggerCounter;

        if (tg.hasOwnProperty(event) === false) {
            tg[event] = 0;
        }

        return ++tg[event];
    }

    /**
     * @param {Function} callbacks
     * @param {Event} event
     * @returns {boolean}
     */
    static _triggerProcess(callbacks, event)
    {
        let i, r, response = true;

        for (i = 0; i < callbacks.length; i++) {
            if (callbacks[i] !== undefined) {
                r = callbacks[i](event.data, event);
                if (r !== undefined) {
                    response = response && r;
                }
            }
        }

        return response;
    }
}

EventManager.global = new EventManager();

export { EventManager, Event, CallbackHandler };