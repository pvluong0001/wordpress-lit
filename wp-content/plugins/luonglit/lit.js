jQuery(document).ready(function () {

    function xml2json (xml, tab) {
        var X = {
            toObj: function (xml) {
                var o = {}
                if (xml.nodeType == 1) {   // element node ..
                    if (xml.attributes.length)   // element with attributes  ..
                        for (var i = 0; i < xml.attributes.length; i++)
                            o['@' + xml.attributes[i].nodeName] = (xml.attributes[i].nodeValue || '').toString()
                    if (xml.firstChild) { // element has child nodes ..
                        var textChild = 0, cdataChild = 0, hasElementChild = false
                        for (var n = xml.firstChild; n; n = n.nextSibling) {
                            if (n.nodeType == 1) hasElementChild = true
                            else if (n.nodeType == 3 && n.nodeValue.match(/[^ \f\n\r\t\v]/)) textChild++ // non-whitespace text
                            else if (n.nodeType == 4) cdataChild++ // cdata section node
                        }
                        if (hasElementChild) {
                            if (textChild < 2 && cdataChild < 2) { // structured element with evtl. a single text or/and cdata node ..
                                X.removeWhite(xml)
                                for (var n = xml.firstChild; n; n = n.nextSibling) {
                                    if (n.nodeType == 3)  // text node
                                        o['#text'] = X.escape(n.nodeValue)
                                    else if (n.nodeType == 4)  // cdata node
                                        o['#cdata'] = X.escape(n.nodeValue)
                                    else if (o[n.nodeName]) {  // multiple occurence of element ..
                                        if (o[n.nodeName] instanceof Array)
                                            o[n.nodeName][o[n.nodeName].length] = X.toObj(n)
                                        else
                                            o[n.nodeName] = [o[n.nodeName], X.toObj(n)]
                                    } else  // first occurence of element..
                                        o[n.nodeName] = X.toObj(n)
                                }
                            } else { // mixed content
                                if (!xml.attributes.length)
                                    o = X.escape(X.innerXml(xml))
                                else
                                    o['#text'] = X.escape(X.innerXml(xml))
                            }
                        } else if (textChild) { // pure text
                            if (!xml.attributes.length)
                                o = X.escape(X.innerXml(xml))
                            else
                                o['#text'] = X.escape(X.innerXml(xml))
                        } else if (cdataChild) { // cdata
                            if (cdataChild > 1)
                                o = X.escape(X.innerXml(xml))
                            else
                                for (var n = xml.firstChild; n; n = n.nextSibling)
                                    o['#cdata'] = X.escape(n.nodeValue)
                        }
                    }
                    if (!xml.attributes.length && !xml.firstChild) o = null
                } else if (xml.nodeType == 9) { // document.node
                    o = X.toObj(xml.documentElement)
                } else
                    alert('unhandled node type: ' + xml.nodeType)
                return o
            },
            toJson: function (o, name, ind) {
                var json = name ? ('"' + name + '"') : ''
                if (o instanceof Array) {
                    for (var i = 0, n = o.length; i < n; i++)
                        o[i] = X.toJson(o[i], '', ind + '\t')
                    json += (name ? ':[' : '[') + (o.length > 1 ? ('\n' + ind + '\t' + o.join(',\n' + ind + '\t') + '\n' + ind) : o.join('')) + ']'
                } else if (o == null)
                    json += (name && ':') + 'null'
                else if (typeof (o) == 'object') {
                    var arr = []
                    for (var m in o)
                        arr[arr.length] = X.toJson(o[m], m, ind + '\t')
                    json += (name ? ':{' : '{') + (arr.length > 1 ? ('\n' + ind + '\t' + arr.join(',\n' + ind + '\t') + '\n' + ind) : arr.join('')) + '}'
                } else if (typeof (o) == 'string')
                    json += (name && ':') + '"' + o.toString() + '"'
                else
                    json += (name && ':') + o.toString()
                return json
            },
            innerXml: function (node) {
                var s = ''
                if ('innerHTML' in node)
                    s = node.innerHTML
                else {
                    var asXml = function (n) {
                        var s = ''
                        if (n.nodeType == 1) {
                            s += '<' + n.nodeName
                            for (var i = 0; i < n.attributes.length; i++)
                                s += ' ' + n.attributes[i].nodeName + '="' + (n.attributes[i].nodeValue || '').toString() + '"'
                            if (n.firstChild) {
                                s += '>'
                                for (var c = n.firstChild; c; c = c.nextSibling)
                                    s += asXml(c)
                                s += '</' + n.nodeName + '>'
                            } else
                                s += '/>'
                        } else if (n.nodeType == 3)
                            s += n.nodeValue
                        else if (n.nodeType == 4)
                            s += '<![CDATA[' + n.nodeValue + ']]>'
                        return s
                    }
                    for (var c = node.firstChild; c; c = c.nextSibling)
                        s += asXml(c)
                }
                return s
            },
            escape: function (txt) {
                return txt.replace(/[\\]/g, '\\\\')
                    .replace(/[\"]/g, '\\"')
                    .replace(/[\n]/g, '\\n')
                    .replace(/[\r]/g, '\\r')
            },
            removeWhite: function (e) {
                e.normalize()
                for (var n = e.firstChild; n;) {
                    if (n.nodeType == 3) {  // text node
                        if (!n.nodeValue.match(/[^ \f\n\r\t\v]/)) { // pure whitespace text node
                            var nxt = n.nextSibling
                            e.removeChild(n)
                            n = nxt
                        } else
                            n = n.nextSibling
                    } else if (n.nodeType == 1) {  // element node
                        X.removeWhite(n)
                        n = n.nextSibling
                    } else                      // any other node
                        n = n.nextSibling
                }
                return e
            }
        }
        if (xml.nodeType == 9) // document node
            xml = xml.documentElement
        var json = X.toJson(X.toObj(X.removeWhite(xml)), xml.nodeName, '\t')
        return '{\n' + tab + (tab ? json.replace(/\t/g, tab) : json.replace(/\t|\n/g, '')) + '\n}'
    }

    function getParameterByName (name, url) {
        if (!url) url = window.location.href
        name = name.replace(/[\[\]]/g, '\\$&')
        var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
            results = regex.exec(url)
        if (!results) return null
        if (!results[2]) return ''
        return decodeURIComponent(results[2].replace(/\+/g, ' '))
    }

    function getLang (v) {
        return new Promise((res, rej) => {
            jQuery.ajax({
                url: GET_LANG_API.replace('VID', v),
                type: 'GET',
                dataType: 'xml',
                success: response => {
                    let objRes = JSON.parse(xml2json(response, ''))

                    try {
                        let tracks = objRes.transcript_list.track
                        let track

                        if (tracks.length == 1) {
                            track = track[0]
                        } else {
                            track = tracks.find(item => {
                                return item.hasOwnProperty('@lang_default')
                            })
                        }

                        if (track) {
                            jQuery.ajax({
                                url: GET_TRANSCRIPT_API.replace('LANGVID', track['@lang_code']).replace('VID', v),
                                type: 'GET',
                                dataType: 'xml',
                                success: response2 => {
                                    try {
                                        let transcriptObj = JSON.parse(xml2json(response2, ''))

                                        let text = transcriptObj.transcript.text.map(item => item['#text']).join('</br>')

                                        jQuery('#result').append(`
                                                <div>
                                                    <h3>${track['@lang_translated']}</h3>
                                                    <blockquote>
                                                    ${singleContent = text.replace(/&amp;#39;/g, '\'')}
                                                    </blockquote>
                                                </div>
                                            `)
                                    } catch (e) {}
                                }
                            })
                        }
                    } catch (e) {
                        error: e => {
                            jQuery.notify('Phát sinh lỗi!', {
                                className: 'error',
                                position: 'bottom right'
                            })
                        }
                    }

                },
                error: e => {
                    jQuery.notify('Phát sinh lỗi!', {
                        className: 'error',
                        position: 'bottom right'
                    })
                }
            })
        })
    }

    jQuery('#get-single').click(() => {
        singleVid = getParameterByName('v', jQuery('#single-link').val())

        getLang(singleVid)
    })

    jQuery('#add-single-post').click(() => {
        let links = jQuery('#single-link').val()
        let categories = jQuery('#single-categories').val()

        if (links.length && categories.length) {
            jQuery.ajax({
                url: litAjax.addpost,
                method: 'POST',
                data: {
                    post_category: categories,
                    vids: links.map(link => getParameterByName('v', link))
                },
                success: response => {
                    if (response.status) {
                        jQuery.notify('Insert success', {
                            className: 'success',
                            position: 'bottom right'
                        })

                        jQuery('#single-link').val('')
                        jQuery('#single-categories').val([]).trigger('change')
                    } else {
                        jQuery.notify('Phát sinh lỗi!', {
                            className: 'error',
                            position: 'bottom right'
                        })
                    }
                },
                error: e => {
                    jQuery.notify('Phát sinh lỗi!', {
                        className: 'error',
                        position: 'bottom right'
                    })
                }
            })
        }
    })

    function getUrl(obj) {
        try {
            return obj.snippet.thumbnails.default.url
        } catch (e) {
            return ''
        }
    }

    jQuery('#fetch-link-btn').click(() => {
        let keywords = jQuery('#multi-search').val()
        let number = jQuery('#number').val()
        jQuery('#multi-result').html('')

        if(keywords.length && number) {
            jQuery.ajax({
                url: litAjax.fetchlink,
                method: 'POST',
                data: {
                    keywords, number
                },
                success: response => {
                    try {
                        if(response) {
                            response.forEach(({key, data}) => {
                                jQuery('#multi-result').append(`
                                    <div>
                                        <div class="row">
                                            <div class="col-12">
                                                <h2 class="float-left">Keyword: ${key}</h2>
                                                <button class="btn btn-warning float-right import-btn" data-vidids="${data.items.map(item => item.id.videoId)}">Import</button>
                                            </div>
                                        </div>
                                        <div>
                                            ${
                                                data.items.map(item => {
                                                    return `
                                                        <div class="row form-group">
                                                            <div class="col-2"><img src="${getUrl(item)}" class="img-response" style="width: 100%;" alt=""></div>
                                                            <div class="col-8"><h5 data-id="${item.id.videoId}">${item.snippet.title}</h5></div>
                                                            <div class="col-2"></div>
                                                        </div>
                                                    `
                                                }).join('')
                                            }
                                        </div>
                                    </div>
                                `)
                            })

                            jQuery.notify('Fetch link thành công', {
                                className: 'success',
                                position: 'bottom right'
                            })
                        }
                    } catch (e) {
                        jQuery.notify('Fetch link không thành công', {
                            className: 'error',
                            position: 'bottom right'
                        })
                    }
                },
                error: e => {
                    jQuery.notify('Phát sinh lỗi!', {
                        className: 'error',
                        position: 'bottom right'
                    })
                }
            })
        }
    })

    jQuery(document).on('click', '.import-btn', function() {
        let vidIds = jQuery(this).data('vidids').split(',')
        let categories = jQuery('#multi-categories').val()

        jQuery(this).attr('disabled', 'disabled')
        
        if(vidIds.length && categories.length) {
            jQuery.notify('Vui lòng chờ đợi quá trình import bài viết', {
                className: 'success',
                position: 'bottom right'
            })

            jQuery.ajax({
                url: litAjax.importmulti,
                type: 'POST',
                data: {
                    vidIds,
                    categories
                },
                success: response => {
                    if(response) {
                        try {
                            response.videoHasNoSub.forEach(vid => {
                                jQuery(`[data-id="${vid}"]`).addClass('l-remove')
                            })


                        } catch (e) {
                            jQuery.notify('Phát sinh lỗi!', {
                                className: 'error',
                                position: 'bottom right'
                            })
                        }
                    }
                }
            })
        }
    })

    jQuery('#get-sub-btn').click(() => {
        let videoIds = jQuery('#single-link').val().map(link => getParameterByName('v', link))
        let lang = jQuery('#single-languages').val()
        let links = [];
        
        jQuery.ajax({
            url: litAjax.get_videos_name,
            method: 'POST',
            data: {videoIds, lang},
            success: response => {
                if(response && response.length) {
                    let html = response.map(({videoId, title, length700}) => {
                        let lengthAvaiable = length700 < 700;

                        if(lengthAvaiable) {
                            links.push(videoId)
                        }

                        return `
                            <tr>
                                <td>${title}</td>
                                <td>${videoId}</td>
                                <td>${length700 ? (lengthAvaiable ? 'Chưa đến 700 ký tự' : 'Thành công') : 'Không có sub/Không thể tải sub'}</td>
                            </tr>
                        `
                    }).join('')

                    jQuery('#single-result').html(html)

                    jQuery('#add-single-post').attr('data-videoids', links)
                }
            }
        })
    })

    jQuery('.l-select2').select2()
    jQuery('.l-m-select2').select2({
        tags: true
    })
})