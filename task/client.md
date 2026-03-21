# Task Frontend

> 3 FE dev, mỗi người có phần chuyên môn riêng nhưng phải review chéo cho nhau

## FE 1: Core Canvas & Math

| # | Task | Trạng thái | Phase | Dependencies | Ghi chú |
|---|------|-----------|-------|-------------|---------|
| 1 | Setup canvas element full screen | ⬚ | 2 | - | xử lí devicePixelRatio |
| 2 | Tích hợp Rough.js, test vẽ thử | ⬚ | 2 | #1 | npm install roughjs |
| 3 | Hàm `createElement(type, x, y, w, h)` | ⬚ | 2 | #2 | trả về element object |
| 4 | Hàm `drawElement(rc, ctx, element)` | ⬚ | 2 | #2 | switch theo type |
| 5 | Hàm `renderScene(elements)` | ⬚ | 2 | #3, #4 | clear + loop vẽ tất cả |
| 6 | Thuật toán hit testing cho rectangle | ⬚ | 4 | #5 | point in rect |
| 7 | Thuật toán hit testing cho ellipse | ⬚ | 4 | #5 | point in ellipse formula |
| 8 | Thuật toán hit testing cho line/arrow | ⬚ | 4 | #5 | distance point to segment |
| 9 | Export PNG (toDataURL) | ⬚ | 5 | #5 | nút download |

**review chéo**: review PR của FE 2 (state, undo/redo) để hiểu state management

## FE 2: State & Events

| # | Task | Trạng thái | Phase | Dependencies | Ghi chú |
|---|------|-----------|-------|-------------|---------|
| 1 | Tạo appState object | ⬚ | 2 | - | elements[], selectedId, currentTool |
| 2 | Xử lí mousedown event | ⬚ | 2 | FE1 #1 | tuỳ tool đang chọn: vẽ mới / select / move |
| 3 | Xử lí mousemove event | ⬚ | 2 | #2 | preview khi đang kéo |
| 4 | Xử lí mouseup event | ⬚ | 2 | #2 | finalize element, push vào history |
| 5 | Select logic (dùng hit test của FE 1) | ⬚ | 4 | FE1 #6-8 | highlight element được chọn |
| 6 | Move logic (drag selected element) | ⬚ | 4 | #5 | cập nhật x, y |
| 7 | Text tool (textarea overlay) | ⬚ | 4 | #1 | tạo input HTML overlay lên canvas |
| 8 | Undo stack | ⬚ | 4 | #1 | lưu snapshot mảng elements |
| 9 | Redo stack | ⬚ | 4 | #8 | historyIndex |
| 10 | Keyboard shortcuts (Ctrl+Z, Delete) | ⬚ | 4 | #8 | addEventListener keydown |
| 11 | Delete element (soft delete) | ⬚ | 4 | #5 | isDeleted = true |

**review chéo**: review PR của FE 1 (drawing, hit testing) để hiểu rendering pipeline

## FE 3: UI & API Caller

| # | Task | Trạng thái | Phase | Dependencies | Ghi chú |
|---|------|-----------|-------|-------------|---------|
| 1 | Toolbar component (tool buttons) | ⬚ | 4 | - | clone UI excalidraw |
| 2 | Style panel (color picker, stroke width) | ⬚ | 4 | - | |
| 3 | Top bar (undo, redo, save, export buttons) | ⬚ | 4 | - | |
| 4 | Login/Register form UI | ⬚ | 5 | - | |
| 5 | Dashboard page (list boards) | ⬚ | 5 | - | grid card layout |
| 6 | `fetchRegister(username, password)` | ⬚ | 5 | BE API #2 | sync với BE |
| 7 | `fetchLogin(username, password)` | ⬚ | 5 | BE API #3 | |
| 8 | `fetchLogout()` | ⬚ | 5 | BE API #4 | |
| 9 | `fetchBoards()` | ⬚ | 5 | BE API #5 | |
| 10 | `fetchCreateBoard(title)` | ⬚ | 5 | BE API #6 | |
| 11 | `fetchBoard(id)` | ⬚ | 5 | BE API #7 | |
| 12 | `fetchUpdateBoard(id, content)` | ⬚ | 5 | BE API #8 | |
| 13 | `fetchDeleteBoard(id)` | ⬚ | 5 | BE API #9 | |
| 14 | Auto-save logic (debounce) | ⬚ | 5 | #12 | gọi update mỗi 5s nếu có thay đổi |

**review chéo**: ngồi trực tiếp với BE để đảm bảo request/response format khớp nhau

## Progress

(cập nhật khi bắt đầu code)