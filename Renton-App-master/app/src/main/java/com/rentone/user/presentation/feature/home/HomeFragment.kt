package com.rentone.user.presentation.feature.home

import android.Manifest
import android.content.ClipData
import android.content.ClipboardManager
import android.content.Context
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
import androidx.fragment.app.activityViewModels
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView
import com.rentone.user.R
import com.rentone.user.core.common.UiState
import com.rentone.user.databinding.FragmentHomeBinding
import com.rentone.user.presentation.feature.login.LoginActivity
import com.rentone.user.presentation.feature.register.customer.RegisterCustomerActivity
import com.rentone.user.presentation.feature.rentvehicle.listvehicle.RentVehicleListVehicleActivity
import com.rentone.user.presentation.feature.rentvehicle.selectregency.RentVehicleSelectRegencyActivity
import com.rentone.user.core.util.ViewUtils
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch

@AndroidEntryPoint
class HomeFragment : Fragment() {

    private var _binding: FragmentHomeBinding? = null
    private val binding get() = _binding!!
    private val viewModel: HomeViewModel by activityViewModels()

    private val requestPermissionsLauncher = registerForActivityResult(
        ActivityResultContracts.RequestMultiplePermissions()
    ) { permissions ->
        if (permissions.all { it.value }) {
            startActivity(Intent(requireContext(), RegisterCustomerActivity::class.java))
        } else {
            showPermissionDeniedDialog()
        }
    }

    override fun onCreateView(
        inflater: LayoutInflater,
        container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View {
        _binding = FragmentHomeBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)
        setupListeners()
        observeState()
        viewModel.fetchHomeData()
    }

    private fun setupListeners() {
        binding.menuRentCarOnly.setOnClickListener {
            val intent = Intent(requireContext(), RentVehicleSelectRegencyActivity::class.java).apply {
                putExtra("param", hashMapOf("functional_type" to "2", "with_driver" to "0"))
            }
            startActivity(intent)
        }

        binding.menuRentCarWithDriver.setOnClickListener {
            val intent = Intent(requireContext(), RentVehicleSelectRegencyActivity::class.java).apply {
                putExtra("param", hashMapOf("functional_type" to "2", "with_driver" to "1"))
            }
            startActivity(intent)
        }

        binding.menuRentMotorcycle.setOnClickListener {
            val intent = Intent(requireContext(), RentVehicleSelectRegencyActivity::class.java).apply {
                putExtra("param", hashMapOf("functional_type" to "1", "with_driver" to "0"))
            }
            startActivity(intent)
        }

        binding.menuRentClosest.setOnClickListener {
            val intent = Intent(requireContext(), RentVehicleListVehicleActivity::class.java).apply {
                putExtra("param", hashMapOf("sort" to "5"))
            }
            startActivity(intent)
        }

        binding.btnLogin.setOnClickListener {
            startActivity(Intent(requireContext(), LoginActivity::class.java))
        }

        binding.btnRegister.setOnClickListener {
            requestPermissionsLauncher.launch(
                arrayOf(
                    Manifest.permission.ACCESS_FINE_LOCATION,
                    Manifest.permission.ACCESS_COARSE_LOCATION,
                    Manifest.permission.CAMERA,
                    Manifest.permission.READ_EXTERNAL_STORAGE,
                    Manifest.permission.WRITE_EXTERNAL_STORAGE
                )
            )
        }

        binding.srLayout.setOnRefreshListener {
            viewModel.fetchHomeData()
        }
    }

    private fun observeState() {
        viewLifecycleOwner.lifecycleScope.launch {
            viewLifecycleOwner.repeatOnLifecycle(Lifecycle.State.STARTED) {
                viewModel.homeState.collect { state ->
                    binding.srLayout.isRefreshing = state is UiState.Loading
                    when (state) {
                        is UiState.Success -> {
                            val data = state.data
                            binding.mainContainer.isVisible = true
                            binding.mainShimmer.isVisible = false
                            binding.mainShimmer.stopShimmer()
                            
                            // Update Balance & Points
                            if (data.balance != null) {
                                binding.containerBalance.isVisible = true
                                binding.containerLogin.isVisible = false
                                binding.txtBalance.text = ViewUtils.formatCurrency(data.balance.balance)
                                binding.txtPoint.text = data.balance.point.toString()
                                binding.txtReferalCode.text = data.referralCode
                            } else {
                                binding.containerBalance.isVisible = false
                                binding.containerLogin.isVisible = true
                            }
                        }
                        is UiState.Error -> {
                            binding.mainShimmer.isVisible = false
                            binding.mainShimmer.stopShimmer()
                            Toast.makeText(requireContext(), state.message, Toast.LENGTH_SHORT).show()
                        }
                        is UiState.Loading -> {
                            binding.mainContainer.isVisible = false
                            binding.mainShimmer.isVisible = true
                            binding.mainShimmer.startShimmer()
                        }
                        else -> Unit
                    }
                }
            }
        }
    }

    private fun showPermissionDeniedDialog() {
        AlertDialog.Builder(requireContext())
            .setTitle(R.string.permission)
            .setMessage(R.string.permission_not_granted)
            .setPositiveButton(android.R.string.ok, null)
            .show()
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}
