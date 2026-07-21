package com.nusatim.sapiriku.presentation.feature.customer.transaction.rentvehicle
import android.content.Intent
import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.Toast
import androidx.core.view.isVisible
import androidx.fragment.app.Fragment
import androidx.fragment.app.viewModels
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import androidx.recyclerview.widget.LinearLayoutManager
import com.nusatim.sapiriku.R
import com.nusatim.sapiriku.custom.PaginationListener
import com.nusatim.sapiriku.databinding.FragmentCustomerRentVehicleTransactionBinding
import com.nusatim.sapiriku.presentation.feature.customer.transaction.rentvehicle.adapter.ListCustomerRentVehicleTransactionAdapter
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch
import com.nusatim.sapiriku.core.util.applyExitTransition
import com.nusatim.sapiriku.presentation.feature.customer.rentvehicle.transactiondetail.CustomerRentVehicleTransactionDetailActivity

@AndroidEntryPoint
class CustomerRentVehicleTransactionFragment : Fragment() {

    private var _binding: FragmentCustomerRentVehicleTransactionBinding? = null
    private val binding get() = _binding!!

    private val viewModel: CustomerRentVehicleTransactionViewModel by viewModels()
    private lateinit var adapter: ListCustomerRentVehicleTransactionAdapter

    override fun onCreateView(inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?): View {
        _binding = FragmentCustomerRentVehicleTransactionBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        adapter = ListCustomerRentVehicleTransactionAdapter(
            onItemClick = { transaction ->
                val intent = Intent(requireContext(), CustomerRentVehicleTransactionDetailActivity::class.java)
                intent.putExtra("id", transaction.id)
                startActivity(intent)
                requireActivity().applyExitTransition(R.anim.slide_in_right, R.anim.slide_out_left)
            }
        )

        val layoutManager = LinearLayoutManager(requireContext())
        binding.list.layoutManager = layoutManager
        binding.list.adapter = adapter
        binding.list.addOnScrollListener(object : PaginationListener(layoutManager) {
            override fun loadMoreItems() = viewModel.loadMore()
            override fun isLastPage() = viewModel.isLastPage
            override fun isLoading() = viewModel.isLoadingMore
        })
        binding.srLayout.setOnRefreshListener { viewModel.loadFirstPage(isRefresh = true) }

        binding.cgFilterStatus.setOnCheckedStateChangeListener { _, checkedIds ->
            val checkedId = checkedIds.firstOrNull() ?: -1
            val status = when (checkedId) {
                R.id.chipWaitingVerification -> 1
                R.id.chipWaitingSchedule -> 2
                R.id.chipOnDelivery -> 3
                R.id.chipWaitingPickup -> 4
                R.id.chipInUse -> 5
                R.id.chipFinishingRenting -> 6
                R.id.chipIsPickingUp -> 7
                R.id.chipOrderCompleted -> 8
                R.id.chipOvertime -> 9
                R.id.chipCanceled -> 11
                else -> -1
            }
            viewModel.updateStatus(status)
        }

        observeState()
        viewModel.loadFirstPage()
    }

    private fun observeState() {
        viewLifecycleOwner.lifecycleScope.launch {
            viewLifecycleOwner.repeatOnLifecycle(Lifecycle.State.STARTED) {
                viewModel.uiState.collect { state ->
                    binding.srLayout.isRefreshing = state.isRefreshing
                    adapter.setLoading(state.isLoadingMore)
                    adapter.submitList(state.items)

                    if (state.isInitialLoading) {
                        binding.shimmer.isVisible = true
                        binding.shimmer.startShimmer()
                    } else {
                        binding.shimmer.stopShimmer()
                        binding.shimmer.isVisible = false
                    }

                    val isEmpty = state.items.isEmpty() && !state.isInitialLoading
                    binding.list.isVisible = !isEmpty
                    binding.txtListMessage.isVisible = isEmpty

                    if (state.error != null) {
                        Toast.makeText(requireContext(), getString(R.string.failed_check_to_server), Toast.LENGTH_LONG).show()
                    }
                }
            }
        }
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}
