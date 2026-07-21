package com.nusatim.sapiriku.core.ui.base

import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.Toast
import androidx.fragment.app.Fragment
import androidx.viewbinding.ViewBinding
import com.nusatim.sapiriku.core.common.UiState

/**
 * BaseFragment untuk standarisasi seluruh Fragment di aplikasi Sapiriku.
 */
abstract class BaseFragment<VB : ViewBinding> : Fragment() {

    private var _binding: VB? = null
    protected val binding: VB get() = _binding!!

    /**
     * Wajib diimplementasikan: Inflate ViewBinding.
     * Contoh: { inflater, container, attach -> FragmentBinding.inflate(inflater, container, attach) }
     */
    protected abstract val bindingInflater: (LayoutInflater, ViewGroup?, Boolean) -> VB

    override fun onCreateView(
        inflater: LayoutInflater,
        container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View? {
        _binding = bindingInflater(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)
        setupUI()
        setupObserver()
    }

    /**
     * Optional: Inisialisasi komponen UI.
     */
    protected open fun setupUI() {}

    /**
     * Optional: Setup Flow/LiveData observers.
     */
    protected open fun setupObserver() {}

    /**
     * Helper: Menampilkan Toast.
     */
    protected fun showToast(message: String?) {
        message?.let { Toast.makeText(requireContext(), it, Toast.LENGTH_SHORT).show() }
    }

    /**
     * Helper: Menangani State UI umum.
     */
    protected open fun handleUiState(state: UiState<*>, onLoading: (Boolean) -> Unit = {}) {
        onLoading(state is UiState.Loading)
        if (state is UiState.Error) {
            showToast(state.message)
        }
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}
