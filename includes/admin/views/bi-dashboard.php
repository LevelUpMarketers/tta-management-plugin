<?php $tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'events'; ?>
<div id="tta-bi-dashboard" class="wrap">
<?php if ( $tab === 'members' ) : ?>
  <section class="tta-bi-section">
    <h2>Subscription Status</h2>
    <label>Timeframe:
      <select class="tta-bi-range" data-chart="subs">
        <option value="1">Last month</option>
        <option value="3">Last 3 months</option>
        <option value="6">Last 6 months</option>
        <option value="12">Last 12 months</option>
        <option value="24">Last 24 months</option>
      </select>
    </label>
    <p>Counts of all active, cancelled and problem subscriptions.</p>
    <div id="tta-bi-subscription-chart" class="tta-bi-chart"></div>
  </section>

  <section class="tta-bi-section">
    <h2>New Member Signups</h2>
    <label>Timeframe:
      <select class="tta-bi-range" data-chart="signups">
        <option value="1">Last month</option>
        <option value="3">Last 3 months</option>
        <option value="6">Last 6 months</option>
        <option value="12">Last 12 months</option>
        <option value="24">Last 24 months</option>
      </select>
    </label>
    <label class="tta-bi-compare"><input type="checkbox" data-chart="signups"> Compare previous</label>
    <p>Monthly member signups for the selected period.</p>
    <div id="tta-bi-signups-chart" class="tta-bi-chart"></div>
  </section>

  <section class="tta-bi-section">
    <h2>Members by Level</h2>
    <label>Timeframe:
      <select class="tta-bi-range" data-chart="by_level">
        <option value="1">Last month</option>
        <option value="3">Last 3 months</option>
        <option value="6">Last 6 months</option>
        <option value="12">Last 12 months</option>
        <option value="24">Last 24 months</option>
      </select>
    </label>
    <p>Current distribution of membership levels.</p>
    <div id="tta-bi-by-level" class="tta-bi-chart"></div>
  </section>

  <section class="tta-bi-section">
    <h2>Monthly Churn Rate</h2>
    <label>Timeframe:
      <select class="tta-bi-range" data-chart="churn">
        <option value="1">Last month</option>
        <option value="3">Last 3 months</option>
        <option value="6">Last 6 months</option>
        <option value="12">Last 12 months</option>
        <option value="24">Last 24 months</option>
      </select>
    </label>
    <label class="tta-bi-compare"><input type="checkbox" data-chart="churn"> Compare previous</label>
    <p>Percentage of members who cancelled each month.</p>
    <div id="tta-bi-churn" class="tta-bi-chart"></div>
  </section>

<?php elseif ( $tab === 'events' ) : ?>
  <section class="tta-bi-section">
    <h2>Ticket Sales Per Year</h2>
    <label>Timeframe:
      <select class="tta-bi-range" data-chart="ticket_sales">
        <option value="1">Last month</option>
        <option value="3">Last 3 months</option>
        <option value="6">Last 6 months</option>
        <option value="12">Last 12 months</option>
        <option value="24">Last 24 months</option>
      </select>
    </label>
    <p>Aggregate event revenue grouped by year.</p>
    <div id="tta-bi-ticket-sales" class="tta-bi-chart"></div>
  </section>

  <section class="tta-bi-section">
    <h2>Average Tickets Per Event</h2>
    <label>Timeframe:
      <select class="tta-bi-range" data-chart="avg_tickets">
        <option value="1">Last month</option>
        <option value="3">Last 3 months</option>
        <option value="6">Last 6 months</option>
        <option value="12">Last 12 months</option>
        <option value="24">Last 24 months</option>
      </select>
    </label>
    <p>Average tickets sold per event this year.</p>
    <div id="tta-bi-avg-tickets" class="tta-bi-chart"></div>
  </section>

<?php else : ?>
  <section class="tta-bi-section">
    <h2>Monthly Revenue</h2>
    <label>Timeframe:
      <select class="tta-bi-range" data-chart="revenue">
        <option value="1">Last month</option>
        <option value="3">Last 3 months</option>
        <option value="6">Last 6 months</option>
        <option value="12">Last 12 months</option>
        <option value="24">Last 24 months</option>
      </select>
    </label>
    <label class="tta-bi-compare"><input type="checkbox" data-chart="revenue"> Compare previous</label>
    <p>Total revenue from all transactions.</p>
    <div id="tta-bi-revenue-chart" class="tta-bi-chart"></div>
  </section>

  <section class="tta-bi-section">
    <h2>Cumulative Revenue</h2>
    <label>Timeframe:
      <select class="tta-bi-range" data-chart="cumulative">
        <option value="1">Last month</option>
        <option value="3">Last 3 months</option>
        <option value="6">Last 6 months</option>
        <option value="12">Last 12 months</option>
        <option value="24">Last 24 months</option>
      </select>
    </label>
    <label class="tta-bi-compare"><input type="checkbox" data-chart="cumulative"> Compare previous</label>
    <p>Total revenue accrued over time.</p>
    <div id="tta-bi-cumulative" class="tta-bi-chart"></div>
  </section>

  <section class="tta-bi-section">
    <h2>Predicted Revenue Next Month</h2>
    <label>Forecast:
      <select class="tta-bi-range" data-chart="prediction">
        <option value="0.25">1 Week Out</option>
        <option value="1">1 Month Out</option>
        <option value="3">3 Months Out</option>
        <option value="6">6 Months Out</option>
      </select>
    </label>
    <p>Forecasted revenue for the selected future period.</p>
    <div id="tta-bi-prediction" class="tta-bi-chart"></div>
  </section>
<?php endif; ?>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/d3/7.9.0/d3.min.js"></script>
<script>
(function(){
  const selects=document.querySelectorAll('.tta-bi-range');
  const compares=document.querySelectorAll('.tta-bi-compare input');
  const map={subs:'#tta-bi-subscription-chart',signups:'#tta-bi-signups-chart',revenue:'#tta-bi-revenue-chart',cumulative:'#tta-bi-cumulative',ticket_sales:'#tta-bi-ticket-sales',avg_tickets:'#tta-bi-avg-tickets',by_level:'#tta-bi-by-level',churn:'#tta-bi-churn',prediction:'#tta-bi-prediction'};
  const tooltip=d3.select('body').append('div').attr('class','tta-bi-tooltip').style('visibility','hidden');
  const valFmt=v=>'$'+(+v).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2});

  function load(sel){
    const months=sel.value;
    const chart=sel.dataset.chart;
    const cmp=document.querySelector(`.tta-bi-compare input[data-chart="${chart}"]`);
    const compare=cmp && cmp.checked ? 1 : 0;
    fetch(`${ajaxurl}?action=tta_bi_data&chart=${chart}&months=${months}&compare=${compare}`)
      .then(r=>r.json()).then(data=>draw(chart,data));
  }

  function draw(chart,data){
    const sel=map[chart];
    if(!sel)return;
    document.querySelector(sel).innerHTML='';
    switch(chart){
      case 'subs': renderBar(sel, data.subs, 'count','Subscriptions'); break;
      case 'signups': renderLine(sel, data.signups,'count','Signups', data.signups_prev); break;
      case 'revenue': renderLine(sel, data.revenue,'amount','Revenue', data.revenue_prev); break;
      case 'cumulative': renderLine(sel, data.cumulative,'amount','Revenue', data.cumulative_prev); break;
      case 'ticket_sales': renderBar(sel, data.ticket_sales,'amount','Sales'); break;
      case 'avg_tickets': renderLine(sel, data.avg_tickets,'count','Tickets'); break;
      case 'by_level': renderPie(sel, data.by_level,'count'); break;
      case 'churn': renderLine(sel, data.churn,'rate','% Churn', data.churn_prev); break;
      case 'prediction': renderBar(sel, [data.prediction],'amount','Revenue'); break;
    }
  }

  selects.forEach(s=>{s.addEventListener('change',()=>load(s)); load(s);});
  compares.forEach(c=>c.addEventListener('change',()=>{
    const chart=c.dataset.chart;
    const sel=document.querySelector(`select[data-chart="${chart}"]`);
    if(sel) load(sel);
  }));

  function renderBar(sel,d,val,label){
    const svg=d3.select(sel).append('svg').attr('width',620).attr('height',320);
    const x=d3.scaleBand().domain(d.map(s=>s.label)).range([60,580]).padding(0.1);
    const y=d3.scaleLinear().domain([0,d3.max(d,s=>+s[val])]).nice().range([260,20]);
    svg.append('g').attr('transform','translate(0,260)').call(d3.axisBottom(x)).selectAll('text').attr('transform','rotate(-45)').style('text-anchor','end');
    svg.append('g').attr('transform','translate(60,0)').call(d3.axisLeft(y));
    svg.append('text').attr('x',10).attr('y',15).text(label);
    svg.append('g').attr('class','grid').attr('transform','translate(60,0)')
      .call(d3.axisLeft(y).ticks(5).tickSize(-520).tickFormat(''));
    svg.selectAll('rect').data(d).enter().append('rect')
      .attr('x',s=>x(s.label))
      .attr('y',260)
      .attr('width',x.bandwidth())
      .attr('height',0)
      .attr('fill','#21759b')
      .on('mousemove',(e,s)=>tooltip.style('left',e.pageX+'px').style('top',(e.pageY-28)+'px').style('visibility','visible').text(s.label+': '+(val==='amount'?valFmt(s[val]):s[val])))
      .on('mouseout',()=>tooltip.style('visibility','hidden'))
      .transition().duration(600)
      .attr('y',s=>y(+s[val]))
      .attr('height',s=>260-y(+s[val]));
  }

  function renderLine(sel,d,val,label,prev){
    const svg=d3.select(sel).append('svg').attr('width',620).attr('height',320);
    const x=d3.scaleBand().domain(d.map(s=>s.label)).range([60,580]).padding(0.1);
    const y=d3.scaleLinear().domain([0,d3.max([...d,...(prev||[])],s=>+s[val])]).nice().range([260,20]);
    svg.append('g').attr('transform','translate(0,260)').call(d3.axisBottom(x)).selectAll('text').attr('transform','rotate(-45)').style('text-anchor','end');
    svg.append('g').attr('transform','translate(60,0)').call(d3.axisLeft(y));
    svg.append('text').attr('x',10).attr('y',15).text(label);
    svg.append('g').attr('class','grid').attr('transform','translate(60,0)')
      .call(d3.axisLeft(y).ticks(5).tickSize(-520).tickFormat(''));
    const line=d3.line().x(s=>x(s.label)+x.bandwidth()/2).y(s=>y(+s[val]));
    svg.append('path').datum(d).attr('fill','none').attr('stroke','#d54e21').attr('stroke-width',2).attr('d',line);
    svg.selectAll('circle').data(d).enter().append('circle')
      .attr('cx',s=>x(s.label)+x.bandwidth()/2)
      .attr('cy',s=>y(+s[val]))
      .attr('r',3)
      .attr('fill','#d54e21')
      .on('mousemove',(e,s)=>tooltip.style('left',e.pageX+'px').style('top',(e.pageY-28)+'px').style('visibility','visible').text(s.label+': '+(val==='amount'?valFmt(s[val]):s[val])))
      .on('mouseout',()=>tooltip.style('visibility','hidden'));
    if(prev){
      const line2=d3.line().x(s=>x(s.label)+x.bandwidth()/2).y(s=>y(+s[val]));
      svg.append('path').datum(prev).attr('fill','none').attr('stroke','#888').attr('stroke-width',2).style('stroke-dasharray','4 2').attr('d',line2);
      svg.selectAll('circle.prev').data(prev).enter().append('circle').attr('class','prev')
        .attr('cx',s=>x(s.label)+x.bandwidth()/2)
        .attr('cy',s=>y(+s[val]))
        .attr('r',3).attr('fill','#888');
      const legend=d3.select(sel).append('div').attr('class','tta-bi-legend');
      legend.append('span').style('color','#d54e21').text('■ Current');
      legend.append('span').style('color','#888').text('■ Previous');
    }
  }

  function renderPie(sel,d,val){
    const w=300,h=300,r=150;
    const svg=d3.select(sel).append('svg').attr('width',w).attr('height',h).append('g').attr('transform','translate('+r+','+r+')');
    const pie=d3.pie().value(s=>s[val]);
    const arc=d3.arc().innerRadius(0).outerRadius(r);
    const color=d3.scaleOrdinal(d3.schemeCategory10);
    const arcs=svg.selectAll('arc').data(pie(d)).enter().append('g');
    arcs.append('path').attr('d',arc).attr('fill',(d,i)=>color(i))
      .on('mousemove',(e,s)=>tooltip.style('left',e.pageX+'px').style('top',(e.pageY-28)+'px').style('visibility','visible').text(s.data.label+': '+(val==='amount'?valFmt(s.data[val]):s.data[val])))
      .on('mouseout',()=>tooltip.style('visibility','hidden'));
    arcs.append('text').attr('transform',d=>`translate(${arc.centroid(d)})`).attr('dy','0.35em').attr('text-anchor','middle').text(d=>d.data.label);
    const legend=d3.select(sel).append('div').attr('class','tta-bi-legend');
    d.forEach((s,i)=>{legend.append('span').style('color',color(i)).text('■ '+s.label+' ');});
  }
})();
</script>
