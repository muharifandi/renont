package com.rentone.user.presentation.feature.rentvehicle.listvehicle
import android.app.Activity
import android.content.Intent
import android.os.Bundle
import android.view.MenuItem
import android.widget.Toast
import androidx.activity.result.contract.ActivityResultContracts
import androidx.activity.viewModels
import androidx.core.view.isVisible
import androidx.appcompat.app.AppCompatActivity
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import androidx.recyclerview.widget.LinearLayoutManager
import com.rentone.user.R
import com.rentone.user.presentation.feature.common.listsort.ListSortActivity
import com.rentone.user.core.util.ViewUtils
import com.rentone.user.core.util.getSerializableExtraCompat
import com.rentone.user.custom.PaginationListener
import com.rentone.user.databinding.ActivityRentVehicleListVehicleBinding
import com.rentone.user.domain.model.FilterList
import com.rentone.user.presentation.feature.rentvehicle.listvehicle.adapter.ListVehicleAdapter
import com.rentone.user.presentation.feature.filter.ListFilterActivity
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch
import com.rentone.user.core.util.applyExitTransition
import com.rentone.user.presentation.feature.rentvehicle.itemdetail.VehicleItemDetailActivity
import com.rentone.user.presentation.feature.rentvehicle.selectregency.RentVehicleSelectRegencyActivity
import com.rentone.user.presentation.feature.rentvehicle.datepicker.RentVehicleDatePickerActivity

@AndroidEntryPoint
class RentVehicleListVehicleActivity : AppCompatActivity() {

    private lateinit var binding: ActivityRentVehicleListVehicleBinding
    private val viewModel: RentVehicleListVehicleViewModel by viewModels()
    private lateinit var adapter: ListVehicleAdapter

    private val filterLauncher = registerForActivityResult(
        ActivityResultContracts.StartActivityForResult()
    ) { result ->
        if (result.resultCode == Activity.RESULT_OK) {
            val filter = result.data?.getSerializableExtraCompat<FilterList>("filter")
            if (filter != null) viewModel.updateFilter(filter)
        }
    }

    private val sortLauncher = registerForActivityResult(
        ActivityResultContracts.StartActivityForResult()
    ) { result ->
        if (result.resultCode == Activity.RESULT_OK) {
            viewModel.updateSort(result.data?.getIntExtra("sort", 0) ?: 0)
        }
    }

    private val param: HashMap<String, String> by lazy {
        intent.getSerializableExtraCompat<HashMap<String, String>>("param") ?: HashMap()
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityRentVehicleListVehicleBinding.inflate(layoutInflater)
        setContentView(binding.root)

        instance = this

        setSupportActionBar(binding.toolbar)
        supportActionBar?.apply {
            setDisplayHomeAsUpEnabled(true)
            setDisplayShowHomeEnabled(true)
        }

        viewModel.param = param
        viewModel.sortIndex = param["sort"]?.toIntOrNull() ?: 0

        binding.txtRegency.text = param["regency_name"] ?: "-"

        setupList()
        setupListeners()
        observeState()

        binding.txtInfo.text = if (param["start_date"] != null || param["end_date"] != null) {
            val days = ViewUtils.getCountOfDays(param["start_date"].orEmpty(), param["end_date"].orEmpty())
            getString(
                R.string.rent_info,
                ViewUtils.mysqlDateToNormalDate(param["start_date"].orEmpty(), "yyyy-MM-dd", "dd MMM yyyy"),
                ViewUtils.mysqlDateToNormalDate(param["end_date"].orEmpty(), "yyyy-MM-dd", "dd MMM yyyy"),
                days
            )
        } else {
            getString(R.string.date_not_selected)
        }

        viewModel.loadFirstPage()
    }

    private fun setupList() {
        adapter = ListVehicleAdapter(
            onItemClick = { vehicle ->
                val intent = Intent(this, VehicleItemDetailActivity::class.java).apply {
                    putExtra("id", vehicle.id)
                    putExtra("param", param)
                    flags = Intent.FLAG_ACTIVITY_FORWARD_RESULT
                }
                startActivity(intent)
                applyExitTransition(R.anim.slide_in_right, R.anim.slide_out_left)
            }
        )

        val layoutManager = LinearLayoutManager(this)
        binding.list.layoutManager = layoutManager
        binding.list.adapter = adapter
        binding.list.addOnScrollListener(object : PaginationListener(layoutManager) {
            override fun loadMoreItems() = viewModel.loadMore()
            override fun isLastPage() = viewModel.uiState.value.isLastPage
            override fun isLoading() = viewModel.uiState.value.isLoadingMore
        })
        binding.srLayout.setOnRefreshListener { viewModel.loadFirstPage(isRefresh = true) }
    }

    private fun setupListeners() {
        binding.filterMenu.setOnClickListener {
            val intent = Intent(this, ListFilterActivity::class.java).apply {
                putExtra("filter", viewModel.uiState.value.filterList)
                putExtra("maxPassenger", true)
                putExtra("priceRange", true)
                putExtra("vehicleFunctionalType", true)
            }
            filterLauncher.launch(intent)
            applyExitTransition(R.anim.slide_in_right, R.anim.slide_out_left)
        }

        binding.sortMenu.setOnClickListener {
            val items = arrayOf(
                getString(R.string.relevance),
                getString(R.string.alfabetic_asc),
                getString(R.string.alfabetic_desc),
                getString(R.string.price_high),
                getString(R.string.price_low),
                getString(R.string.closest)
            )
            val intent = Intent(this, ListSortActivity::class.java).apply {
                putExtra("list_sort", items)
                putExtra("sort", viewModel.sortIndex)
            }
            sortLauncher.launch(intent)
            applyExitTransition(R.anim.slide_in_right, R.anim.slide_out_left)
        }
    }

    private fun observeState() {
        lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                viewModel.uiState.collect { state ->
                    binding.srLayout.isRefreshing = state.isRefreshing
                    adapter.showDistance = viewModel.sortIndex == 5
                    adapter.setLoading(state.isLoadingMore)
                    adapter.submitList(state.vehicles)

                    if (state.isInitialLoading) {
                        binding.shimmer.isVisible = true
                        binding.shimmer.startShimmer()
                    } else {
                        binding.shimmer.stopShimmer()
                        binding.shimmer.isVisible = false
                    }

                    val isEmpty = state.vehicles.isEmpty() && !state.isInitialLoading
                    binding.list.isVisible = !isEmpty
                    binding.txtListMessage.isVisible = isEmpty

                    if (state.error != null) {
                        Toast.makeText(this@RentVehicleListVehicleActivity, getString(R.string.failed_check_to_server), Toast.LENGTH_LONG).show()
                    }
                }
            }
        }
    }

    override fun onOptionsItemSelected(item: MenuItem): Boolean {
        if (item.itemId == android.R.id.home) {
            onBackPressedDispatcher.onBackPressed()
        }
        return true
    }

    override fun finish() {
        RentVehicleSelectRegencyActivity.instance?.finish()
        RentVehicleDatePickerActivity.instance?.finish()
        super.finish()
        applyExitTransition(R.anim.slide_in_left, R.anim.slide_out_right)
    }

    companion object {
        var instance: RentVehicleListVehicleActivity? = null
    }
}
