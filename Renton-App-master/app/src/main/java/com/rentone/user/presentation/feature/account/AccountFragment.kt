package com.rentone.user.presentation.feature.account

import android.content.Intent
import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.Toast
import androidx.activity.result.contract.ActivityResultContracts
import androidx.appcompat.app.AlertDialog
import androidx.core.view.isVisible
import androidx.fragment.app.Fragment
import androidx.fragment.app.viewModels
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import coil.load
import com.rentone.user.R
import com.rentone.user.core.common.UiState
import com.rentone.user.databinding.FragmentAccountBinding
import com.rentone.user.core.util.ViewUtils
import com.rentone.user.presentation.feature.home.HomeActivity
import com.rentone.user.presentation.feature.login.LoginActivity
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch

@AndroidEntryPoint
class AccountFragment : Fragment() {

    private var _binding: FragmentAccountBinding? = null
    private val binding get() = _binding!!
    private val viewModel: AccountViewModel by viewModels()

    private val pickImageLauncher = registerForActivityResult(ActivityResultContracts.GetContent()) { uri ->
        uri?.let {
            // Upload image logic here (convert URI to MultipartBody.Part)
        }
    }

    override fun onCreateView(
        inflater: LayoutInflater,
        container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View {
        _binding = FragmentAccountBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)
        setupListeners()
        observeState()
        viewModel.getCustomerDetail()
    }

    private fun setupListeners() {
        binding.txtLogout.setOnClickListener {
            showLogoutConfirmDialog()
        }

        binding.profileImage.setOnClickListener {
            pickImageLauncher.launch("image/*")
        }

        binding.srLayout.setOnRefreshListener {
            viewModel.getCustomerDetail()
        }
    }

    private fun observeState() {
        viewLifecycleOwner.lifecycleScope.launch {
            viewLifecycleOwner.repeatOnLifecycle(Lifecycle.State.STARTED) {
                viewModel.customerDetail.collect { state ->
                    binding.srLayout.isRefreshing = state is UiState.Loading
                    when (state) {
                        is UiState.Success -> {
                            val detail = state.data
                            binding.txtName.text = "${detail.customerDetail?.firstName} ${detail.customerDetail?.lastName}"
                            binding.profileImage.load(detail.customerDetail?.imgProfile)
                            // Update other UI fields
                        }
                        is UiState.Error -> {
                            Toast.makeText(requireContext(), state.message, Toast.LENGTH_SHORT).show()
                        }
                        else -> Unit
                    }
                }
            }
        }
    }

    private fun showLogoutConfirmDialog() {
        AlertDialog.Builder(requireContext())
            .setTitle(R.string.logout)
            .setMessage(R.string.logout_confirm)
            .setPositiveButton(android.R.string.ok) { _, _ ->
                viewModel.logout()
                startActivity(Intent(requireContext(), LoginActivity::class.java))
                requireActivity().finish()
            }
            .setNegativeButton(android.R.string.cancel, null)
            .show()
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}
