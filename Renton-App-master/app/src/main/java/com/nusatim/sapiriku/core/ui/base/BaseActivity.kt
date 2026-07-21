package com.nusatim.sapiriku.core.ui.base

import android.os.Bundle
import android.view.LayoutInflater
import android.view.MenuItem
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.viewbinding.ViewBinding
import com.nusatim.sapiriku.core.common.UiState
import com.nusatim.sapiriku.core.util.applyExitTransition

/**
 * BaseActivity untuk standarisasi seluruh Activity di aplikasi Sapiriku.
 * Mendukung ViewBinding secara otomatis dan penanganan UI yang konsisten.
 */
abstract class BaseActivity<VB : ViewBinding> : AppCompatActivity() {

    private var _binding: VB? = null
    protected val binding: VB get() = _binding!!

    /**
     * Wajib diimplementasikan: Inflate ViewBinding.
     * Contoh: { ActivityBinding.inflate(it) }
     */
    protected abstract val bindingInflater: (LayoutInflater) -> VB

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        
        // 1. Setup ViewBinding (Wajib)
        _binding = bindingInflater(layoutInflater)
        setContentView(binding.root)

        // 2. Setup Edge-to-Edge/Window Insets (Optional)
        if (useEdgeToEdge) {
            setupEdgeToEdge()
        }

        // 3. Lifecycle hooks
        setupUI()
        setupObserver()
    }

    /**
     * Optional: Set true jika ingin mendukung tampilan layar penuh (status bar transparan).
     */
    protected open val useEdgeToEdge: Boolean = false

    /**
     * Optional: Inisialisasi komponen UI (Listeners, Adapters, dll).
     */
    protected open fun setupUI() {}

    /**
     * Optional: Setup Flow/LiveData observers.
     */
    protected open fun setupObserver() {}

    /**
     * Helper: Menampilkan Toast secara konsisten.
     */
    protected fun showToast(message: String?) {
        message?.let { Toast.makeText(this, it, Toast.LENGTH_SHORT).show() }
    }

    /**
     * Helper: Menangani State UI secara umum (Loading, Error).
     * Bisa di-override jika butuh penanganan khusus.
     */
    protected open fun handleUiState(state: UiState<*>, onLoading: (Boolean) -> Unit = {}) {
        onLoading(state is UiState.Loading)
        if (state is UiState.Error) {
            showToast(state.message)
        }
    }

    /**
     * Helper: Setup standard toolbar dengan tombol kembali.
     */
    protected fun setupToolbar(
        toolbar: androidx.appcompat.widget.Toolbar?,
        title: String? = null,
        showBackButton: Boolean = true
    ) {
        setSupportActionBar(toolbar)
        supportActionBar?.apply {
            this.title = title
            setDisplayHomeAsUpEnabled(showBackButton)
            setDisplayShowHomeEnabled(showBackButton)
        }
    }

    private fun setupEdgeToEdge() {
        // Implementasi WindowInsets modern di sini
    }

    override fun onOptionsItemSelected(item: MenuItem): Boolean {
        if (item.itemId == android.R.id.home) {
            onBackPressedDispatcher.onBackPressed()
            return true
        }
        return super.onOptionsItemSelected(item)
    }

    override fun onDestroy() {
        super.onDestroy()
        _binding = null
    }

    override fun finish() {
        super.finish()
        // Standard exit animation untuk user experience yang mulus
        applyExitTransition(com.nusatim.sapiriku.R.anim.slide_in_left, com.nusatim.sapiriku.R.anim.slide_out_right)
    }
}
