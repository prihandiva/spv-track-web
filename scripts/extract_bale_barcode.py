import sys
import json
import re
import os
import subprocess
from PIL import Image

def extract_barcode(image_path):
    if not os.path.exists(image_path):
        return {"success": False, "message": "File not found"}

    try:
        import zxingcpp
        has_zxing = True
    except ImportError:
        has_zxing = False

    tesseract_exe = r"C:\Program Files\Tesseract-OCR\tesseract.exe"
    has_tesseract = os.path.exists(tesseract_exe)

    try:
        img = Image.open(image_path)
    except Exception as e:
        return {"success": False, "message": f"Cannot open image: {str(e)}"}

    # 1. Try ZXing with downscale and rotations (0, 90, 180, 270)
    if has_zxing:
        # Resize if huge
        max_dim = 1600
        w, h = img.size
        scaled_img = img
        if w > max_dim or h > max_dim:
            scaled_img = img.copy()
            scaled_img.thumbnail((max_dim, max_dim), Image.Resampling.LANCZOS)

        for rot in [0, 90, 270, 180]:
            rimg = scaled_img.rotate(rot, expand=True) if rot else scaled_img
            try:
                results = zxingcpp.read_barcodes(rimg, try_rotate=True, try_downscale=True)
                if results:
                    for r in results:
                        txt = r.text.strip().strip('*').upper()
                        if txt:
                            return {"success": True, "barcode": txt, "method": "zxing"}
            except Exception:
                pass

    # 2. Try Tesseract OCR for text printed beside barcode (*P058473786* or P058473786)
    if has_tesseract:
        for rot in [0, 90, 270]:
            rimg = img.rotate(rot, expand=True) if rot else img
            tmp_path = image_path + f"_rot_{rot}.png"
            try:
                rimg.save(tmp_path)
                cmd = [tesseract_exe, tmp_path, "stdout", "--psm", "11"]
                proc = subprocess.run(cmd, capture_output=True, text=True, timeout=10)
                out_text = proc.stdout

                # Pattern A: *P058473786* or *123456*
                m_star = re.search(r'\*([A-Za-z0-9]{6,16})\*', out_text)
                if m_star:
                    clean = m_star.group(1).upper()
                    return {"success": True, "barcode": clean, "method": "ocr_star"}

                # Pattern B: P followed by 8-11 digits (e.g. P058473786, P058473806)
                m_p = re.search(r'\b(P\d{8,11})\b', out_text, re.I)
                if m_p:
                    return {"success": True, "barcode": m_p.group(1).upper(), "method": "ocr_p"}

                # Pattern C: Lenzing bale format 05.847378
                m_dot = re.search(r'\b(\d{2})\.(\d{6})\b', out_text)
                if m_dot:
                    # Construct P + 8 digits
                    bale_code = f"P{m_dot.group(1)}{m_dot.group(2)}"
                    return {"success": True, "barcode": bale_code, "method": "ocr_dot"}
            except Exception:
                pass
            finally:
                if os.path.exists(tmp_path):
                    try:
                        os.remove(tmp_path)
                    except Exception:
                        pass

    return {"success": False, "message": "Barcode tidak terdeteksi"}

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"success": False, "message": "No file argument"}))
        sys.exit(1)
    res = extract_barcode(sys.argv[1])
    print(json.dumps(res))

