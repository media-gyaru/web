(function () {
  const canvas = document.querySelector('[data-board-canvas]');
  const form = document.querySelector('[data-board-form]');

  if (!canvas || !form) {
    return;
  }

  const ctx = canvas.getContext('2d');
  const colorInput = document.querySelector('[data-canvas-color]');
  const sizeInput = document.querySelector('[data-canvas-size]');
  const imageInput = document.querySelector('[data-canvas-image]');
  const clearButton = document.querySelector('[data-clear-canvas]');

  let isDrawing = false;
  let hasDrawing = false;

  function getPoint(event) {
    const rect = canvas.getBoundingClientRect();
    const source = event.touches ? event.touches[0] : event;

    return {
      x: ((source.clientX - rect.left) / rect.width) * canvas.width,
      y: ((source.clientY - rect.top) / rect.height) * canvas.height,
    };
  }

  function beginDraw(event) {
    event.preventDefault();
    isDrawing = true;
    const point = getPoint(event);
    ctx.beginPath();
    ctx.moveTo(point.x, point.y);
  }

  function draw(event) {
    if (!isDrawing) {
      return;
    }

    event.preventDefault();
    const point = getPoint(event);

    ctx.lineWidth = Number(sizeInput.value || 4);
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    ctx.strokeStyle = colorInput.value || '#1d2528';
    ctx.lineTo(point.x, point.y);
    ctx.stroke();
    hasDrawing = true;
  }

  function endDraw(event) {
    if (event) {
      event.preventDefault();
    }

    isDrawing = false;
    ctx.beginPath();
  }

  function clearCanvas() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    hasDrawing = false;
    imageInput.value = '';
  }

  canvas.addEventListener('mousedown', beginDraw);
  canvas.addEventListener('mousemove', draw);
  window.addEventListener('mouseup', endDraw);

  canvas.addEventListener('touchstart', beginDraw, { passive: false });
  canvas.addEventListener('touchmove', draw, { passive: false });
  canvas.addEventListener('touchend', endDraw, { passive: false });
  canvas.addEventListener('touchcancel', endDraw, { passive: false });

  clearButton.addEventListener('click', clearCanvas);

  form.addEventListener('submit', function () {
    imageInput.value = hasDrawing ? canvas.toDataURL('image/png') : '';
  });
})();
