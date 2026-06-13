<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BPMN Process Editor — {{ $system->name }}</title>
    <link rel="stylesheet" href="{{ asset('bpmn/diagram-js.css') }}">
    <link rel="stylesheet" href="{{ asset('bpmn/bpmn.css') }}">
    <script src="{{ asset('bpmn/bpmn-modeler.development.js') }}"></script>
    <script src="{{ asset('js/jquery-3.3.1.min.js') }}"></script>
    <link href="{{ asset('landing/assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <script src="{{ asset('bpmn/popper.min.js') }}"></script>
    <script src="{{ asset('landing/assets/js/vendors/bootstrap.min.js') }}"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        html, body, #canvas {
            height: 100%;
            padding: 0;
            margin: 0;
        }

        #canvas {
            border: 1px solid #ccc;
        }

        #button-container {
            position: fixed;
            bottom: 20px;
            left: 20px;
            display: flex;
            gap: 10px;
        }

        .custom-button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .custom-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<div id="canvas"></div>

<div id="button-container" class="text-center mt-3">
    <button id="save-bpmn" class="custom-button">Save as BPMN</button>
    <button id="save-png" class="custom-button" style="background-color: #28a745;">Save as PNG</button>
    <button id="save-webp" class="custom-button" style="background-color: #17a2b8;">Save as WebP</button>
    <button id="save-button" class="custom-button" onclick="showNameModal()">Save Process</button>
</div>

<div class="modal fade" id="nameModal" tabindex="-1" role="dialog" aria-labelledby="nameModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="nameModalLabel">Save BPMN Process</h5>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="diagramName">Process Name</label>
                    <input type="text" class="form-control" id="diagramName" placeholder="Enter process name">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveDiagram">Save</button>
            </div>
        </div>
    </div>
</div>

<script>
    var diagramUrl = '{!! asset('bpmn/diagram.bpmn') !!}';
    var bpmnModeler = new BpmnJS({
        container: '#canvas',
        keyboard: {
            bindTo: window
        }
    });

    $.get(diagramUrl, openDiagram, 'text');

    function openDiagram(bpmnXML) {
        bpmnModeler.importXML(bpmnXML, function (err) {
            if (err) {
                return console.error('could not import BPMN 2.0 diagram', err);
            }
            bpmnModeler.get('canvas').zoom('fit-viewport');
        });
    }

    document.getElementById('save-bpmn').addEventListener('click', function () {
        exportDiagram('bpmn');
    });

    document.getElementById('save-png').addEventListener('click', function () {
        exportDiagram('png');
    });

    document.getElementById('save-webp').addEventListener('click', function () {
        exportDiagram('webp');
    });

    function exportDiagram(type) {
        if (type === 'bpmn') {
            bpmnModeler.saveXML({format: true}, function (err, xml) {
                if (err) {
                    return console.error('could not save BPMN 2.0 diagram', err);
                }
                downloadFile('diagram.bpmn', xml, 'application/xml');
            });
        } else {
            bpmnModeler.saveSVG({}, function (err, svg) {
                if (err) {
                    return console.error('could not save BPMN 2.0 diagram', err);
                }
                convertSvgToImage(svg, type);
            });
        }
    }

    function convertSvgToImage(svg, type) {
        var canvas = document.createElement('canvas');
        var ctx = canvas.getContext('2d');
        var img = new Image();

        img.onload = function () {
            canvas.width = img.width;
            canvas.height = img.height;
            ctx.drawImage(img, 0, 0);
            canvas.toBlob(function (blob) {
                var url = URL.createObjectURL(blob);
                var a = document.createElement('a');
                a.href = url;
                a.download = 'diagram.' + type;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            }, 'image/' + type);
        };

        img.src = 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(svg)));
    }

    function downloadFile(filename, content, mimeType) {
        var blob = new Blob([content], {type: mimeType});
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }

    function showNameModal() {
        $('#nameModal').modal('show');
    }

    $('#saveDiagram').on('click', function () {
        var diagramName = $('#diagramName').val().trim();
        if (diagramName === '') {
            alert('Please enter a process name.');
            return;
        }
        $('#nameModal').modal('hide');
        bpmnModeler.saveXML({format: true}, function (err, xml) {
            if (err) {
                return console.error('could not save BPMN 2.0 diagram', err);
            }

            $.ajax({
                type: 'POST',
                url: '{{ route('systems.store.bpmn') }}',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    name: diagramName,
                    diagram_xml: xml,
                    system_id: {{ $system->id }},
                },
                success: function () {
                    alert('Process saved successfully!');
                    window.location.href = '{{ route('systems.processes', $system) }}';
                },
                error: function (xhr, status, error) {
                    console.error('Error saving process: ', error);
                    alert('Failed to save process.');
                }
            });
        });
    });
</script>

</body>
</html>
