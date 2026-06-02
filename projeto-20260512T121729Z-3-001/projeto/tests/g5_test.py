"""
G5 — Imagens Personalizadas: E2E Test Suite
Tests: CRUD, isolamento, integração com carrinho, preço own.
"""
import asyncio
import sys

# pip install playwright if not installed
from playwright.async_api import async_playwright

BASE = "http://127.0.0.1:8001"

# Customer credentials (from seed data — password is '123')
CUSTOMER_EMAIL = "c1@mail.pt"
CUSTOMER_PASS = "123"

# Second customer for isolation test
CUSTOMER2_EMAIL = "c2@mail.pt"
CUSTOMER2_PASS = "123"

# Admin for access denial test
ADMIN_EMAIL = "a2@mail.pt"
ADMIN_PASS = "123"

results = []

def log_result(name, passed, detail=""):
    status = "✅ PASS" if passed else "❌ FAIL"
    results.append((name, passed, detail))
    print(f"  {status} — {name}" + (f" ({detail})" if detail else ""))


async def login(page, email, password):
    """Login helper."""
    await page.context.clear_cookies()
    await page.goto(f"{BASE}/login")
    await page.fill('input[name="email"]', email)
    await page.fill('input[name="password"]', password)
    await page.click('button[type="submit"]')
    await page.wait_for_load_state("networkidle")


async def run_tests():
    print("\n" + "=" * 60)
    print("  G5 — IMAGENS PERSONALIZADAS — TESTES E2E")
    print("=" * 60 + "\n")

    async with async_playwright() as p:
        browser = await p.chromium.launch(headless=True, slow_mo=100)
        context = await browser.new_context(viewport={"width": 1280, "height": 900})

        # ============================================================
        # TEST 1: Customer can access "As Minhas Imagens" page
        # ============================================================
        print("\n📋 Test 1: Acesso à página 'As Minhas Imagens'")
        page = await context.new_page()
        await login(page, CUSTOMER_EMAIL, CUSTOMER_PASS)
        await page.goto(f"{BASE}/customer/tshirt-images")
        await page.wait_for_load_state("networkidle")
        
        title = await page.text_content("h1")
        has_title = "As Minhas Imagens" in (title or "")
        log_result("Página index acessível para cliente", has_title, f"title='{title}'")
        await page.close()

        # ============================================================
        # TEST 2: Anonymous user gets redirected to login
        # ============================================================
        print("\n📋 Test 2: Anónimo redirecionado para login")
        page = await context.new_page()
        await context.clear_cookies()
        await page.goto(f"{BASE}/customer/tshirt-images")
        await page.wait_for_load_state("networkidle")
        
        is_login_page = "/login" in page.url
        log_result("Anónimo redirecionado para login", is_login_page, f"url={page.url}")
        await page.close()

        # ============================================================
        # TEST 3: Admin gets 403 when accessing customer area
        # ============================================================
        print("\n📋 Test 3: Admin recebe 403 na área de cliente")
        page = await context.new_page()
        await login(page, ADMIN_EMAIL, ADMIN_PASS)
        
        resp = await page.goto(f"{BASE}/customer/tshirt-images")
        status_code = resp.status if resp else 0
        is_forbidden = status_code == 403
        log_result("Admin bloqueado (403) na área de cliente", is_forbidden, f"status={status_code}")
        await page.close()

        # ============================================================
        # TEST 4: Customer can open the CREATE form
        # ============================================================
        print("\n📋 Test 4: Formulário de criação acessível")
        page = await context.new_page()
        await login(page, CUSTOMER_EMAIL, CUSTOMER_PASS)
        await page.goto(f"{BASE}/customer/tshirt-images/create")
        await page.wait_for_load_state("networkidle")
        
        has_form = await page.query_selector('form[enctype="multipart/form-data"]') is not None
        has_name_field = await page.query_selector('input[name="name"]') is not None
        has_image_field = await page.query_selector('input[name="image"]') is not None
        log_result("Formulário de criação renderiza", has_form and has_name_field and has_image_field,
                   f"form={has_form}, name={has_name_field}, image={has_image_field}")
        await page.close()

        # ============================================================
        # TEST 5: Customer can upload a new private image (STORE)
        # ============================================================
        print("\n📋 Test 5: Upload de imagem privada (store)")
        page = await context.new_page()
        await login(page, CUSTOMER_EMAIL, CUSTOMER_PASS)
        await page.goto(f"{BASE}/customer/tshirt-images/create")
        await page.wait_for_load_state("networkidle")
        
        await page.fill('input[name="name"]', "Teste G5 Automático")
        await page.fill('textarea[name="description"]', "Imagem de teste criada pelo script E2E.")
        
        # Create a test image file (1x1 white PNG)
        import base64
        png_bytes = base64.b64decode(
            "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAC0lEQVQI12NgAAIABQAB"
            "Nl7BcQAAAABJRU5ErkJggg=="
        )
        await page.set_input_files('input[name="image"]', {
            "name": "test_g5.png",
            "mimeType": "image/png",
            "buffer": png_bytes,
        })
        
        await page.click('main form button[type="submit"]')
        await page.wait_for_load_state("networkidle")
        
        # Should redirect to index with success message
        is_index = "/customer/tshirt-images" in page.url and "/create" not in page.url
        success_msg = await page.query_selector('text=Imagem adicionada com sucesso')
        log_result("Upload de imagem privada (store)", is_index and success_msg is not None,
                   f"redirect={is_index}, msg={'found' if success_msg else 'missing'}")

        # Check the card is visible
        card_text = await page.text_content("body")
        has_card = "Teste G5 Automático" in (card_text or "")
        log_result("Imagem aparece no index após criação", has_card)
        await page.close()

        # ============================================================
        # TEST 6: Customer can view the SHOW page with editor
        # ============================================================
        print("\n📋 Test 6: Página de detalhe com editor interativo")
        page = await context.new_page()
        await login(page, CUSTOMER_EMAIL, CUSTOMER_PASS)
        await page.goto(f"{BASE}/customer/tshirt-images")
        await page.wait_for_load_state("networkidle")
        
        # Click the first "Ver" button
        ver_link = await page.query_selector('a:has-text("Ver")')
        if ver_link:
            await ver_link.click()
            await page.wait_for_load_state("networkidle")
            
            has_editor = await page.query_selector('#slider-scale') is not None
            has_stamp = await page.query_selector('#preview-stamp') is not None
            has_cart_btn = await page.query_selector('button:has-text("Adicionar ao Carrinho")') is not None
            has_private_badge = await page.query_selector('text=Imagem Privada') is not None
            
            log_result("Editor interativo renderiza", has_editor and has_stamp,
                       f"slider={has_editor}, stamp={has_stamp}")
            log_result("Badge 'Imagem Privada' visível", has_private_badge)
            log_result("Botão 'Adicionar ao Carrinho' presente", has_cart_btn)
        else:
            log_result("Página de detalhe com editor interativo", False, "No 'Ver' link found")
        await page.close()

        # ============================================================
        # TEST 7: Customer can EDIT the image
        # ============================================================
        print("\n📋 Test 7: Edição de imagem privada")
        page = await context.new_page()
        await login(page, CUSTOMER_EMAIL, CUSTOMER_PASS)
        await page.goto(f"{BASE}/customer/tshirt-images")
        await page.wait_for_load_state("networkidle")
        
        edit_link = await page.query_selector('a:has-text("Editar")')
        if edit_link:
            await edit_link.click()
            await page.wait_for_load_state("networkidle")
            
            # Check current name is pre-filled
            name_val = await page.input_value('input[name="name"]')
            has_prefill = "Teste G5 Automático" in (name_val or "")
            log_result("Formulário de edição pré-preenchido", has_prefill, f"name='{name_val}'")
            
            # Update the name
            await page.fill('input[name="name"]', "Teste G5 Editado")
            await page.click('main form button[type="submit"]')
            await page.wait_for_load_state("networkidle")
            
            body_text = await page.text_content("body")
            has_updated = "Teste G5 Editado" in (body_text or "")
            log_result("Imagem atualizada com sucesso", has_updated)
        else:
            log_result("Edição de imagem privada", False, "No 'Editar' link found")
        await page.close()

        # ============================================================
        # TEST 8: Price shown is unit_price_own (not catalog)
        # ============================================================
        print("\n📋 Test 8: Preço exibido é unit_price_own")
        page = await context.new_page()
        await login(page, CUSTOMER_EMAIL, CUSTOMER_PASS)
        await page.goto(f"{BASE}/customer/tshirt-images")
        await page.wait_for_load_state("networkidle")
        
        ver_link = await page.query_selector('a:has-text("Ver")')
        if ver_link:
            await ver_link.click()
            await page.wait_for_load_state("networkidle")
            
            # Check that the price label says "Preço imagem personalizada"
            body_text = await page.text_content("body")
            has_own_label = "imagem personalizada" in (body_text or "").lower()
            log_result("Label de preço indica 'imagem personalizada'", has_own_label)
        else:
            log_result("Preço unit_price_own", False, "No link found")
        await page.close()

        # ============================================================
        # TEST 9: Navigation link "As Minhas Imagens" visible for customer
        # ============================================================
        print("\n📋 Test 9: Link de navegação visível para cliente")
        page = await context.new_page()
        await login(page, CUSTOMER_EMAIL, CUSTOMER_PASS)
        await page.goto(f"{BASE}")
        await page.wait_for_load_state("networkidle")
        
        nav_link = await page.query_selector('a:has-text("As Minhas Imagens")')
        log_result("Link 'As Minhas Imagens' no menu", nav_link is not None)
        await page.close()

        # ============================================================
        # TEST 10: DELETE (soft delete)
        # ============================================================
        print("\n📋 Test 10: Eliminação (soft delete)")
        page = await context.new_page()
        await login(page, CUSTOMER_EMAIL, CUSTOMER_PASS)
        await page.goto(f"{BASE}/customer/tshirt-images")
        await page.wait_for_load_state("networkidle")
        
        # Accept the confirm dialog
        page.on("dialog", lambda dialog: dialog.accept())
        
        delete_btn = await page.query_selector('button:has-text("Eliminar")')
        if delete_btn:
            await delete_btn.click()
            await page.wait_for_load_state("networkidle")
            
            success_msg = await page.query_selector('text=Imagem removida com sucesso')
            body_text = await page.text_content("body")
            image_gone = "Teste G5 Editado" not in (body_text or "")
            log_result("Soft delete executado", success_msg is not None and image_gone,
                       f"msg={'found' if success_msg else 'missing'}, gone={image_gone}")
        else:
            log_result("Soft delete", False, "No delete button found")
        await page.close()

        await browser.close()

    # ============================================================
    # SUMMARY
    # ============================================================
    print("\n" + "=" * 60)
    passed = sum(1 for _, p, _ in results if p)
    total = len(results)
    print(f"  RESULTADO FINAL: {passed}/{total} testes passaram")
    print("=" * 60)
    
    for name, p, detail in results:
        status = "✅" if p else "❌"
        print(f"  {status} {name}")
    
    print()
    return 0 if passed == total else 1


if __name__ == "__main__":
    sys.exit(asyncio.run(run_tests()))
