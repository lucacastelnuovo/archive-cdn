! function (e) {
	"object" == typeof exports && "object" == typeof module ? e(require("../../lib/codemirror"), require("../fold/xml-fold")) : "function" == typeof define && define.amd ? define(["../../lib/codemirror", "../fold/xml-fold"], e) : e(CodeMirror)
}(function (e) {
	function t(t, n) {
		for (var a = t.listSelections(), r = [], i = n ? "/" : "</", s = t.getOption("autoCloseTags"), l = "object" == typeof s && s.dontIndentOnSlash, c = 0; c < a.length; c++) {
			if (!a[c].empty()) return e.Pass;
			var d = a[c].head,
				f = t.getTokenAt(d),
				g = e.innerMode(t.getMode(), f.state),
				u = g.state;
			if (n && ("string" == f.type || "<" != f.string.charAt(0) || f.start != d.ch - 1)) return e.Pass;
			var m;
			if ("xml" != g.mode.name)
				if ("htmlmixed" == t.getMode().name && "javascript" == g.mode.name) m = i + "script";
				else {
					if ("htmlmixed" != t.getMode().name || "css" != g.mode.name) return e.Pass;
					m = i + "style"
				}
			else {
				if (!u.context || !u.context.tagName || o(t, u.context.tagName, d, u)) return e.Pass;
				m = i + u.context.tagName
			}
			">" != t.getLine(d.line).charAt(f.end) && (m += ">"), r[c] = m
		}
		if (t.replaceSelections(r), a = t.listSelections(), !l)
			for (c = 0; c < a.length; c++)(c == a.length - 1 || a[c].head.line < a[c + 1].head.line) && t.indentLine(a[c].head.line)
	}

	function n(e, t) {
		if (e.indexOf) return e.indexOf(t);
		for (var n = 0, o = e.length; n < o; ++n)
			if (e[n] == t) return n;
		return -1
	}

	function o(t, n, o, a, r) {
		if (!e.scanForClosingTag) return !1;
		var i = Math.min(t.lastLine() + 1, o.line + 500),
			s = e.scanForClosingTag(t, o, null, i);
		if (!s || s.tag != n) return !1;
		for (var l = a.context, c = r ? 1 : 0; l && l.tagName == n; l = l.prev) ++c;
		o = s.to;
		for (var d = 1; d < c; d++) {
			var f = e.scanForClosingTag(t, o, null, i);
			if (!f || f.tag != n) return !1;
			o = f.to
		}
		return !0
	}
	e.defineOption("autoCloseTags", !1, function (i, s, l) {
		if (l != e.Init && l && i.removeKeyMap("autoCloseTags"), s) {
			var c = {
				name: "autoCloseTags"
			};
			("object" != typeof s || s.whenClosing) && (c["'/'"] = function (n) {
				return function (n) {
					return n.getOption("disableInput") ? e.Pass : t(n, !0)
				}(n)
			}), ("object" != typeof s || s.whenOpening) && (c["'>'"] = function (t) {
				return function (t) {
					if (t.getOption("disableInput")) return e.Pass;
					for (var i = t.listSelections(), s = [], l = t.getOption("autoCloseTags"), c = 0; c < i.length; c++) {
						if (!i[c].empty()) return e.Pass;
						var d = i[c].head,
							f = t.getTokenAt(d),
							g = e.innerMode(t.getMode(), f.state),
							u = g.state;
						if ("xml" != g.mode.name || !u.tagName) return e.Pass;
						var m = "html" == g.mode.configuration,
							h = "object" == typeof l && l.dontCloseTags || m && a,
							p = "object" == typeof l && l.indentTags || m && r,
							v = u.tagName;
						f.end > d.ch && (v = v.slice(0, v.length - f.end + d.ch));
						var b = v.toLowerCase();
						if (!v || "string" == f.type && (f.end != d.ch || !/[\"\']/.test(f.string.charAt(f.string.length - 1)) || 1 == f.string.length) || "tag" == f.type && "closeTag" == u.type || f.string.indexOf("/") == f.string.length - 1 || h && n(h, b) > -1 || o(t, v, d, u, !0)) return e.Pass;
						var y = p && n(p, b) > -1;
						s[c] = {
							indent: y,
							text: ">" + (y ? "\n\n" : "") + "</" + v + ">",
							newPos: y ? e.Pos(d.line + 1, 0) : e.Pos(d.line, d.ch + 1)
						}
					}
					var x = "object" == typeof l && l.dontIndentOnAutoClose;
					for (c = i.length - 1; c >= 0; c--) {
						var P = s[c];
						t.replaceRange(P.text, i[c].head, i[c].anchor, "+insert");
						var T = t.listSelections().slice(0);
						T[c] = {
							head: P.newPos,
							anchor: P.newPos
						}, t.setSelections(T), !x && P.indent && (t.indentLine(P.newPos.line, null, !0), t.indentLine(P.newPos.line + 1, null, !0))
					}
				}(t)
			}), i.addKeyMap(c)
		}
	});
	var a = ["area", "base", "br", "col", "command", "embed", "hr", "img", "input", "keygen", "link", "meta", "param", "source", "track", "wbr"],
		r = ["applet", "blockquote", "body", "button", "div", "dl", "fieldset", "form", "frameset", "h1", "h2", "h3", "h4", "h5", "h6", "head", "html", "iframe", "layer", "legend", "object", "ol", "p", "select", "table", "ul"];
	e.commands.closeTag = function (e) {
		return t(e)
	}
});


