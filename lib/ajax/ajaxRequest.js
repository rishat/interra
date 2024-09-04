/******************************************************************************
 * Copyright 2005 Maxim "Maxx" Poltarak <maxx@e-taller.net>.
 * Distributed under the Boost Software License, Version 1.0. (See
 * accompanying file LICENSE_1_0.txt or copy at
 * http://www.boost.org/LICENSE_1_0.txt)
 *****************************************************************************/

function AjaxRequest() {}
(function()
{
	var timeoutDefault = 10 * 1000;	// 10 seconds

	AjaxRequest.prototype =
	{
		info			: null,

		cached			: false,

		isReady			: true,
		isTimedOut		: false,
		timeoutInterval	: null,

		send : function(info)
		{
			try {
				if(!this.isReady)
					throw new Error("The component is occupied.\nYou have to create another one.");
			    this.isReady = false;

				if("object" != typeof(info))
					throw new Error("Wrong parameter to send() method");

				if(!(info.conn = this.getConnection()))
					throw new Error("Can't create connection.");

				var self = this;
				info.conn.onreadystatechange = function() { self.onReadyStateChange(); }
				info.parent = this;

				if("boolean" != typeof(info.async)) info.async = true;

				if("string" != typeof(info.url) || !info.url.length)
					throw new Error("Please, set the URL.");

				if("string" != typeof(info.method)) info.method = "post";
				info.method = info.method.toLowerCase();

				var queryString = ("object" == typeof(info.data))
					? this.serialize(info.data)
					: escape(info.data);

				if("post" != info.method)
				{
					if(!this.cached) info.url += (info.url.indexOf("?") < 0 ? "?" : "&")
						+ "ajaxRequestUncache=" + parseInt(Math.random() * 1000000)

					if(queryString.length > 0) info.url += (info.url.indexOf("?") < 0 ? "?" : "&")
						+ queryString;
				}

				if("string" != typeof(info.username)) info.username = null;
				if("string" != typeof(info.password)) info.password = null;

				info.timeout = "number" == typeof(info.timeout)
					? Math.max(0, info.timeout)
					: timeoutDefault;
				this.isTimedOut = false;
				if(info.timeout > 0)
					this.timeoutInterval = setTimeout(function() { self.onTimeout(); }, info.timeout);

				info.result = {};
				this.info = info;

				info.conn.open(info.method, info.url, info.async, info.username, info.password);
				try {
					info.conn.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

					if("post" == info.method) 
						info.conn.setRequestHeader("Content-length", queryString.length);
				} catch(e) {}
				info.conn.send("post" == info.method ? queryString : null);

				if(!info.async)
				{
					this.info.result = this.getResult();
					return this.info.result;
				}
				
			} catch(e) {
				alert("AjaxRequest error\n\n" + e.message);
			}

		},

		onReadyStateChange : function()
		{
			switch(this.info.conn.readyState)
			{
				case 0 : break;

				case 1 :
					if(this.info.onConnecting) this.info.onConnecting(this.info);
					break;

				case 2 :
					if(this.info.onConnected) this.info.onConnected(this.info);
					break;

				case 3 :
					if(this.info.onData) this.info.onData(this.info);
					break;

				case 4 :
					this.isReady = true;
					if(this.isTimedOut) break;
					if(this.timeoutInterval)
					{
						clearInterval(this.timeoutInterval);
						this.timeoutInterval = null;
					}

					switch(this.info.conn.status)
					{
						case 200 : // everything's OK
							this.info.result = this.getResult();

							if(this.info.result._ERROR)
							{
								alert("AjaxRequest server side error\n\n"+this.info.result._ERROR);
							}

							if(this.info.onSuccess) this.info.onSuccess(this.info.result, this.info);
							break;

						case 12029 : // timed out by system
							this.onTimeout();
							break;

						default :
							if(this.info.onError) this.info.onError(this.info);
					}
			}
		},

		onTimeout : function()
		{
			if(this.timeoutInterval)
			{
				clearInterval(this.timeoutInterval);
				this.timeoutInterval = null;
			}
			this.isTimedOut = true;
			this.isReady = true;
			try {
				this.info.conn.abort();
			} catch(e) {}
			if(this.info.onTimeout) this.info.onTimeout(this.info);
		},

		getConnection : function()
		{
			var conn = null;

			try {
				conn = new XMLHttpRequest;
			} catch(e) {}

			if(!conn) try
			{
				conn = new ActiveXObject("Msxml2.XMLHTTP");
			} catch(e) {}

			if(!conn) try
			{
				conn = new ActiveXObject("Microsoft.XMLHTTP");
			} catch(e) {}

			if(!conn) alert("AjaxRequest error\n\nYour browser doesn't support Ajax!");

			return conn;
		},

		getResult : function()
		{
			return this.info.conn.responseText.length
				? this.unserializeWddx(this.info.conn.responseXML.getElementsByTagName("struct")[0])
				: {};
		},

		serialize : function(data)
		{
			if("object" != typeof(data) || null === data || data instanceof Array)
			{
				alert("AjaxRequest error\n\nOnly objects can be serialized");
				return "";
			}

			return this.serializeObject("", data).join("&");
		},

		serializeObject : function(prefix, data)
		{
			if(null === data)
			{
				return prefix.length ? [prefix+"="] : [];
			}

			var vars = new Array;

			switch(typeof(data))
			{
				case "object" :
					if(data instanceof Array)
					{
						for(var i=0; i < data.length; i++)
							vars = vars.concat(this.serializeObject(prefix+"["+i+"]", data[i]));
					} else {

						for(var i in data)
							vars = vars.concat(this.serializeObject(
								prefix.length ? prefix+"["+escape(i)+"]" : escape(i),
								data[i]
							));
					}
					break;

				case "string" :
				case "number" :
					vars.push(prefix + "=" + escape(data));
					break;

				case "boolean" :
					vars.push(prefix + "=" + (data ? 1 : 0));
					break;
			}

			return vars;
		},

		unserializeWddx : function(dom)
		{
			if("undefined" == typeof(dom) || "null" == typeof(dom)) return {};

			switch(dom.nodeName)
			{
				case "struct" :
					var result = {};
					for(var i=dom.firstChild; i; i = i.nextSibling)
					{
						if("var" != i.nodeName) continue;
						result[i.getAttribute("name")] = this.unserializeWddx(i.firstChild);
					}
					return result;

				case "array" :
					var result = new Array;
					for(var i=dom.firstChild; i; i = i.nextSibling)
					{
						result.push(this.unserializeWddx(i));
					}
					return result;

				case "string" :
					if(!dom.firstChild) return "";

					if(dom.firstChild.nextSibling)
					{
						var result = "";
						for(var i=dom.firstChild; i; i = i.nextSibling)
						{
							if(1 == i.nodeType && "char" == i.nodeName)
							{
								result += String.fromCharCode(
									parseInt(
										i.getAttribute("code"), 
										16
									)
								);
							} else if(3 == i.nodeType) result += i.nodeValue;
						}
						return result;
					}
					return dom.firstChild.nodeValue;

				case "number" :
					return Number(dom.firstChild.nodeValue);

				case "boolean" :
					return "true" == dom.getAttribute("value") ? true : false;

				case "null" :
					return null;
			}
		}
	}
}) ();
