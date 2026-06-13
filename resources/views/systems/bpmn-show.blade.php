<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $bpmn->name }} — BPMN Process</title>
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
            flex-wrap: wrap;
            gap: 10px;
            z-index: 100;
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

        .custom-button:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
    </style>
</head>
<body>

<div id="canvas"></div>

<div id="button-container" class="text-center mt-3">
    <button id="save-process" class="custom-button" style="background-color: #fd7e14;">Save Changes</button>
    <button id="rename-process" class="custom-button" style="background-color: #6610f2;">Rename</button>
    <button id="save-bpmn" class="custom-button">Export BPMN</button>
    <button id="save-png" class="custom-button" style="background-color: #28a745;">Export PNG</button>
    <button id="save-webp" class="custom-button" style="background-color: #17a2b8;">Export WebP</button>
    @if($bpmn->system)
        <a href="{{ route('systems.processes', $bpmn->system) }}" class="custom-button" style="background-color: #6c757d; text-decoration: none;">Back to Processes</a>
    @endif
</div>

<div class="modal fade" id="nameModal" tabindex="-1" role="dialog" aria-labelledby="nameModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="nameModalLabel">Rename Process</h5>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="diagramName">Process Name</label>
                    <input type="text" class="form-control" id="diagramName" value="{{ $bpmn->name }}">
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
    var processName = @json($bpmn->name);
    var bpmnModeler = new BpmnJS({
        container: '#canvas',
        keyboard: {
            bindTo: window
        }
    });

    var bpmnXML = @json($bpmn->xml);

    openDiagram(bpmnXML);

    function openDiagram(bpmnXML) {
        bpmnModeler.importXML(bpmnXML, function (err) {
            if (err) {
                return console.error('could not import BPMN 2.0 diagram', err);
            }
            bpmnModeler.get('canvas').zoom('fit-viewport');
        });
    }

    function saveProcess(nameOverride) {
        var saveBtn = document.getElementById('save-process');
        saveBtn.disabled = true;
        saveBtn.textContent = 'Saving...';

        bpmnModeler.saveXML({ format: true }, function (err, xml) {
            if (err) {
                saveBtn.disabled = false;
                saveBtn.textContent = 'Save Changes';
                return console.error('could not save BPMN 2.0 diagram', err);
            }

            if (nameOverride) {
                processName = nameOverride;
            }

            $.ajax({
                type: 'POST',
                url: '{{ route('systems.update.bpmn', $bpmn) }}',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    _method: 'PUT',
                    name: processName,
                    diagram_xml: xml,
                },
                success: function () {
                    saveBtn.disabled = false;
                    saveBtn.textContent = 'Save Changes';
                    document.title = processName + ' — BPMN Process';
                    alert('Process saved successfully!');
                },
                error: function (xhr, status, error) {
                    saveBtn.disabled = false;
                    saveBtn.textContent = 'Save Changes';
                    console.error('Error saving process: ', error);
                    alert('Failed to save process.');
                }
            });
        });
    }

    document.getElementById('save-process').addEventListener('click', function () {
        saveProcess();
    });

    document.getElementById('rename-process').addEventListener('click', function () {
        $('#diagramName').val(processName);
        $('#nameModal').modal('show');
    });

    $('#saveDiagram').on('click', function () {
        var newName = $('#diagramName').val().trim();
        if (newName === '') {
            alert('Please enter a process name.');
            return;
        }
        $('#nameModal').modal('hide');
        saveProcess(newName);
    });

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
            bpmnModeler.saveXML({ format: true }, function (err, xml) {
                if (err) {
                    return console.error('could not save BPMN 2.0 diagram', err);
                }
                downloadFile(processName + '.bpmn', xml, 'application/xml');
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
                a.download = processName + '.' + type;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            }, 'image/' + type);
        };

        img.src = 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(svg)));
    }

    function downloadFile(filename, content, mimeType) {
        var blob = new Blob([content], { type: mimeType });
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }
</script>

</body>
</html>
